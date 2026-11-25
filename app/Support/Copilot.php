<?php

namespace App\Support;

use App\Models\Societe;

/**
 * Copilot IA (2026-11-25): the user writes in French, Arabic or Darija; Claude answers with the company's own
 * figures (read-only tools) or prepares a document form (never saved without the user). Bound in the
 * container so tests can swap in a fake (no API call).
 */
class Copilot
{
    public const MODEL = 'claude-sonnet-5-5';

    public const PRICE_IN = 2.0;   // USD per million tokens

    public const PRICE_OUT = 10.0;

    public function isConfigured(): bool
    {
        return (bool) config('services.anthropic.key');
    }

    public static function cost(int $in, int $out): float
    {
        return round($in / 1e6 * self::PRICE_IN + $out / 1e6 * self::PRICE_OUT, 4);
    }

    protected function system(): string
    {
        $s = Societe::current();
        $u = auth()->user();

        return "Tu es le Copilot de Stokito, le logiciel de gestion de stock, devis et factures de l'entreprise « {$s->name} » (pays : {$s->country_code}, devise : ".($s->currency ?: 'EUR').").\n"
            .'Date du jour : '.now()->locale('fr')->isoFormat('dddd D MMMM YYYY').". Utilisateur : {$u->name}.\n"
            ."Règles :\n"
            ."- Réponds dans la langue et l'écriture du dernier message de l'utilisateur : français, arabe, ou darija. Si l'utilisateur écrit en darija en lettres latines, réponds entièrement en darija en lettres latines (noms, numéros et montants tels quels). Réponses courtes et concrètes.\n"
            ."- Dans l'historique, la mention [Outils utilisés : …] indique que ta réponse précédente s'appuyait vraiment sur les outils et que ses boutons sont valables : ne la refais pas et ne la corrige pas, sauf si l'utilisateur le demande.\n"
            ."- N'invente jamais un chiffre, un client, un produit ou un prix : utilise les outils. Si une information manque ou si plusieurs clients ou produits correspondent, pose une question courte.\n"
            ."- Pour un devis, une facture, un bon de livraison ou un bon de commande : cherche le client (ou fournisseur) et chaque produit, puis appelle preparer_document. Le document n'est PAS enregistré : dis à l'utilisateur d'ouvrir le formulaire pré-rempli avec le bouton, de vérifier puis d'enregistrer.\n"
            ."- Les prix du catalogue sont TTC pour la vente ; dans les documents les prix sont HT. « 175 DH HT » = prix_unitaire_ht 175 ; un prix TTC donné par l'utilisateur se convertit avec le taux de TVA du produit.\n"
            ."- Tu ne peux pas envoyer d'email ni de WhatsApp, ni modifier ou supprimer des données : dis-le simplement si on te le demande.\n"
            ."- Hors du travail de l'entreprise (stock, ventes, achats, documents), réponds brièvement que tu sers à la gestion dans Stokito.";
    }

    /**
     * @param  array  $history  previous turns [['role' => 'user'|'assistant', 'text' => string]]
     * @return array{text: string, actions: array, tools: array, input_tokens: int, output_tokens: int}
     *
     * @throws CopilotException
     */
    public function reply(array $history, string $message): array
    {
        $client = new \Anthropic\Client(apiKey: config('services.anthropic.key'));
        $messages = [];
        foreach (array_slice($history, -12) as $h) {
            if (trim((string) ($h['text'] ?? '')) !== '') {
                $text = (string) $h['text'];
                if ($h['role'] === 'assistant' && ! empty($h['tools'])) {
                    $text .= "\n\n[Outils utilisés : ".implode(', ', array_unique($h['tools'])).']';
                }
                $messages[] = ['role' => $h['role'] === 'assistant' ? 'assistant' : 'user', 'content' => $text];
            }
        }
        // the API needs alternating turns starting with the user
        while ($messages && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $in = $out = 0;
        $actions = [];
        $used = [];
        $tools = CopilotTools::definitions();
        for ($round = 0; $round < 8; $round++) {
            try {
                $res = $client->messages->create(
                    model: self::MODEL,
                    maxTokens: 4000,
                    system: $this->system(),
                    tools: $tools,
                    messages: $messages,
                    outputConfig: ['effort' => 'medium'],
                    requestOptions: ['timeout' => 90, 'maxRetries' => 1],
                );
            } catch (\Anthropic\Core\Exceptions\RateLimitException|\Anthropic\Core\Exceptions\OverloadedException|\Anthropic\Core\Exceptions\ServiceUnavailableException $e) {
                throw new CopilotException('Le Copilot est très demandé en ce moment. Réessayez dans une minute.', 0, $e, $in, $out);
            } catch (\Anthropic\Core\Exceptions\APITimeoutException|\Anthropic\Core\Exceptions\APIConnectionException $e) {
                throw new CopilotException('La réponse a pris trop de temps. Réessayez.', 0, $e, $in, $out);
            } catch (\Anthropic\Core\Exceptions\AnthropicException $e) {
                report($e);
                throw new CopilotException('Le Copilot n’est pas disponible pour le moment.', 0, $e, $in, $out);
            }
            $in += (int) ($res->usage->inputTokens ?? 0);
            $out += (int) ($res->usage->outputTokens ?? 0);
            if ($res->stopReason === 'refusal') {
                throw new CopilotException('Je ne peux pas répondre à cette demande.', 0, null, $in, $out);
            }
            if ($res->stopReason !== 'tool_use') {
                $text = collect($res->content)->filter(fn ($b) => ($b->type ?? null) === 'text')->map(fn ($b) => $b->text)->implode("\n");

                return ['text' => trim($text) ?: '…', 'actions' => $actions, 'tools' => $used, 'input_tokens' => $in, 'output_tokens' => $out];
            }
            $results = [];
            foreach ($res->content as $b) {
                if (($b->type ?? null) === 'tool_use') {
                    [$result, $action] = CopilotTools::run($b->name, (array) $b->input);
                    $used[] = $b->name;
                    if ($action) {
                        $actions[] = $action;
                    }
                    $results[] = ['type' => 'tool_result', 'toolUseID' => $b->id, 'content' => json_encode($result, JSON_UNESCAPED_UNICODE)];
                }
            }
            $messages[] = ['role' => 'assistant', 'content' => $res->content];
            $messages[] = ['role' => 'user', 'content' => $results];
        }

        throw new CopilotException('Demande trop longue à traiter : essayez de la découper.', 0, null, $in, $out);
    }
}
