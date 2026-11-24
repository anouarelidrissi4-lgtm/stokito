<?php

namespace App\Support;

use App\Models\Fournisseur;
use App\Models\Product;
use Illuminate\Support\Str;

/**
 * "Scanner une facture" (2026-11-24): a supplier invoice (photo or PDF) is read by Claude and turned into
 * a purchase order draft. scan() calls the API; match() links what was read to the company's own
 * suppliers and products. The user always checks the result before saving.
 * Bound in the container so tests can swap in a fake (no API call, no cost).
 */
class InvoiceScanner
{
    public const MODEL = 'claude-opus-5-5';

    /** USD per million tokens (input, output). */
    public const PRICE_IN = 4.0;

    public const PRICE_OUT = 20.0;

    public function isConfigured(): bool
    {
        return (bool) config('services.anthropic.key');
    }

    public static function cost(int $in, int $out): float
    {
        return round($in / 1e6 * self::PRICE_IN + $out / 1e6 * self::PRICE_OUT, 4);
    }

    /**
     * @return array{data: array, input_tokens: int, output_tokens: int}
     *
     * @throws InvoiceScanException with a message that can be shown to the user
     */
    public function scan(string $path, string $mime): array
    {
        $block = $this->fileBlock($path, $mime);
        $client = new \Anthropic\Client(apiKey: config('services.anthropic.key'));

        try {
            $msg = $client->beta->messages->create(
                model: self::MODEL,
                maxTokens: 16000,
                betas: ['server-side-fallback-2026-07-01'],
                fallbacks: 'default',
                system: self::SYSTEM,
                outputConfig: ['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => self::schema()]],
                messages: [['role' => 'user', 'content' => [
                    $block,
                    ['type' => 'text', 'text' => 'Extrais les données de cette facture fournisseur.'],
                ]]],
                requestOptions: ['timeout' => 120, 'maxRetries' => 1],
            );
        } catch (\Anthropic\Core\Exceptions\RateLimitException|\Anthropic\Core\Exceptions\OverloadedException|\Anthropic\Core\Exceptions\ServiceUnavailableException $e) {
            throw new InvoiceScanException('Le service de lecture est très demandé en ce moment. Réessayez dans une minute.', 0, $e);
        } catch (\Anthropic\Core\Exceptions\APITimeoutException|\Anthropic\Core\Exceptions\APIConnectionException $e) {
            throw new InvoiceScanException('La lecture a pris trop de temps. Réessayez, ou envoyez une photo plus nette.', 0, $e);
        } catch (\Anthropic\Core\Exceptions\BadRequestException|\Anthropic\Core\Exceptions\ContentTooLargeException $e) {
            throw new InvoiceScanException('Ce fichier n’a pas pu être lu. Essayez une photo (JPG) ou un PDF plus léger.', 0, $e);
        } catch (\Anthropic\Core\Exceptions\AnthropicException $e) {
            report($e);
            throw new InvoiceScanException('Le scanner n’est pas disponible pour le moment. Réessayez plus tard.', 0, $e);
        }

        $in = (int) ($msg->usage->inputTokens ?? 0);
        $out = (int) ($msg->usage->outputTokens ?? 0);
        if ($msg->stopReason === 'refusal') {
            throw new InvoiceScanException('Ce document n’a pas pu être analysé.', 0, null, $in, $out);
        }
        $text = null;
        foreach ($msg->content as $b) {
            if (($b->type ?? null) === 'text') {
                $text = $b->text;
                break;
            }
        }
        $data = $text !== null ? json_decode($text, true) : null;
        if (! is_array($data)) {
            throw new InvoiceScanException('La facture n’a pas pu être lue. Essayez une photo plus nette, bien à plat.', 0, null, $in, $out);
        }

        return ['data' => $data, 'input_tokens' => $in, 'output_tokens' => $out];
    }

    /** Image (resized, EXIF-rotated, JPEG) or PDF content block. */
    protected function fileBlock(string $path, string $mime): array
    {
        if ($mime === 'application/pdf') {
            return ['type' => 'document', 'source' => ['type' => 'base64', 'mediaType' => 'application/pdf', 'data' => base64_encode(file_get_contents($path))]];
        }
        $img = @imagecreatefromstring(file_get_contents($path));
        if (! $img) {
            throw new InvoiceScanException('Cette image n’a pas pu être ouverte. Utilisez une photo JPG ou PNG, ou un PDF.');
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data($path)['Orientation'] ?? 1);
            $angle = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($angle) {
                $img = imagerotate($img, $angle, 0);
            }
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $max = 2000;
        if (max($w, $h) > $max) {
            $r = $max / max($w, $h);
            $img = imagescale($img, (int) round($w * $r), (int) round($h * $r), IMG_BICUBIC);
        }
        ob_start();
        imagejpeg($img, null, 85);
        $jpeg = ob_get_clean();

        return ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => 'image/jpeg', 'data' => base64_encode($jpeg)]];
    }

    public const SYSTEM = <<<'TXT'
Tu lis des factures fournisseurs (photo ou PDF) pour un logiciel de gestion de stock utilisé en France et au Maroc.
Recopie ce qui est écrit sur le document, sans rien inventer : une information absente ou illisible vaut null.
- fournisseur : l'entreprise qui émet la facture (pas le client destinataire). identifiant = son ICE, SIRET, SIREN ou n° de TVA intracommunautaire, tel qu'écrit.
- date : la date de la facture, au format AAAA-MM-JJ.
- lignes : un élément par article facturé, dans l'ordre du document. Les frais facturés (transport, emballage…) sont aussi des lignes. Ignore les sous-totaux, acomptes et lignes de texte.
- prix_unitaire_ht : prix unitaire hors taxes après remise de ligne éventuelle. Si seuls des prix TTC figurent, calcule le HT avec le taux de TVA de la ligne.
- taux_tva : en pourcentage (20 pour 20 %).
- remise_globale_ht : montant d'une remise appliquée au total (pas aux lignes), sinon null.
- Nombres en JSON : point décimal, sans espace ni symbole monétaire.
- est_une_facture : false si le document n'est ni une facture, ni un bon de livraison, ni un bon de commande fournisseur.
TXT;

    public static function schema(): array
    {
        $s = ['type' => ['string', 'null']];
        $n = ['type' => ['number', 'null']];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['est_une_facture', 'fournisseur', 'numero_facture', 'date', 'devise', 'lignes', 'remise_globale_ht', 'total_ht', 'total_tva', 'total_ttc'],
            'properties' => [
                'est_une_facture' => ['type' => 'boolean'],
                'fournisseur' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['nom', 'identifiant', 'adresse', 'telephone', 'email'],
                    'properties' => ['nom' => $s, 'identifiant' => $s, 'adresse' => $s, 'telephone' => $s, 'email' => $s],
                ],
                'numero_facture' => $s,
                'date' => $s,
                'devise' => $s,
                'lignes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['designation', 'reference', 'code_barres', 'quantite', 'prix_unitaire_ht', 'taux_tva', 'total_ht'],
                        'properties' => [
                            'designation' => ['type' => 'string'], 'reference' => $s, 'code_barres' => $s,
                            'quantite' => $n, 'prix_unitaire_ht' => $n, 'taux_tva' => $n, 'total_ht' => $n,
                        ],
                    ],
                ],
                'remise_globale_ht' => $n,
                'total_ht' => $n,
                'total_tva' => $n,
                'total_ttc' => $n,
            ],
        ];
    }

    // ---------------------------------------------------------------- matching

    public static function norm(?string $s): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower(Str::ascii((string) $s)));
    }

    /** Supplier by identifier digits (ICE / SIRET / TVA), then by name. */
    public static function matchFournisseur(array $f): ?Fournisseur
    {
        $id = preg_replace('/\D+/', '', (string) ($f['identifiant'] ?? ''));
        $name = self::norm($f['nom'] ?? '');
        $all = Fournisseur::all();
        if (strlen($id) >= 6) {
            $hit = $all->first(fn ($x) => ($d = preg_replace('/\D+/', '', (string) $x->ice)) !== '' && (str_contains($id, $d) || str_contains($d, $id)) && strlen($d) >= 6);
            if ($hit) {
                return $hit;
            }
        }
        if (strlen($name) < 3) {
            return null;
        }

        return $all->first(fn ($x) => self::norm($x->name) === $name)
            ?? $all->first(fn ($x) => strlen($n = self::norm($x->name)) >= 4 && (str_contains($name, $n) || str_contains($n, $name)));
    }

    /**
     * Each line read on the invoice becomes a form line. 'scan' keeps what was read and how sure the match is:
     * ok (reference / barcode / exact name), check (similar name, to confirm), new (unknown product).
     */
    public static function matchLines(array $lignes): array
    {
        $products = Product::all(['id', 'reference', 'barcode', 'name', 'purchase_price']);
        $byRef = $products->filter(fn ($p) => self::norm($p->reference) !== '')->keyBy(fn ($p) => self::norm($p->reference));
        $byCode = $products->filter(fn ($p) => self::norm($p->barcode) !== '')->keyBy(fn ($p) => self::norm($p->barcode));
        $byName = $products->keyBy(fn ($p) => self::norm($p->name));
        $items = [];
        foreach ($lignes as $l) {
            $label = trim((string) ($l['designation'] ?? ''));
            $ref = trim((string) ($l['reference'] ?? ''));
            $code = trim((string) ($l['code_barres'] ?? ''));
            $qty = (float) ($l['quantite'] ?? 0) ?: 1;
            $pu = $l['prix_unitaire_ht'] ?? null;
            if ($pu === null && isset($l['total_ht'])) {
                $pu = (float) $l['total_ht'] / $qty;
            }
            $p = null;
            $status = 'new';
            if ($ref !== '' && ($p = $byRef->get(self::norm($ref)) ?? $byCode->get(self::norm($ref)))) {
                $status = 'ok';
            } elseif ($code !== '' && ($p = $byCode->get(self::norm($code)))) {
                $status = 'ok';
            } elseif ($label !== '' && ($p = $byName->get(self::norm($label)))) {
                $status = 'ok';
            } elseif ($label !== '') {
                $best = 0;
                $a = self::norm($label);
                foreach ($products as $cand) {
                    similar_text($a, self::norm($cand->name), $pct);
                    if ($pct > $best) {
                        [$best, $p] = [$pct, $cand];
                    }
                }
                if ($best >= 80) {
                    $status = 'check';
                } else {
                    $p = null;
                }
            }
            $items[] = [
                'product_id' => $p?->id ?? '',
                'quantity' => $qty,
                'unit_price' => round((float) ($pu ?? $p?->purchase_price ?? 0), 2),
                'scan' => ['text' => $label, 'reference' => $ref, 'barcode' => $code, 'status' => $status, 'tva' => $l['taux_tva'] ?? null],
            ];
        }

        return $items;
    }
}
