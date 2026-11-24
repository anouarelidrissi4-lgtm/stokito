<?php

namespace Tests\Feature;

use App\Models\Devis;
use App\Models\Facture;
use App\Support\WhatsAppShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WhatsAppShareTest extends TestCase
{
    use RefreshDatabase;

    protected function facture(string $numero, ?string $phone = '06 12 34 56 78'): Facture
    {
        $c = \App\Models\Client::create(['name' => 'Client WA', 'phone' => $phone]);
        $f = Facture::create(['client_id' => $c->id, 'numero' => $numero, 'date' => now(), 'due_date' => now()->addDays(30), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $this->product('W'.$numero)->id, 'quantity' => 1, 'unit_price' => 100, 'tva_rate' => 20, 'total' => 100]);
        $f->recalculateTotals();

        return $f->fresh(['client']);
    }

    public function test_customer_opens_the_pdf_from_the_signed_link_without_an_account(): void
    {
        $this->companyUser('FR');
        $link = WhatsAppShare::pdfLink($this->facture('FAC-WA1'));
        Auth::logout();

        $r = $this->get($link)->assertOk();
        $this->assertStringStartsWith('%PDF', $r->getContent());
        $this->assertGuest();
    }

    public function test_a_changed_id_or_a_missing_signature_is_refused(): void
    {
        $this->companyUser('FR');
        $mine = $this->facture('FAC-WA2');
        $this->companyUser('MA');
        $other = $this->facture('FAC-WA3');
        $link = WhatsAppShare::pdfLink($mine);
        Auth::logout();

        $this->get(str_replace('/facture/'.$mine->id.'?', '/facture/'.$other->id.'?', $link))->assertForbidden();
        $this->get('/d/facture/'.$other->id)->assertForbidden();
    }

    public function test_whatsapp_link_uses_the_country_code_and_contains_the_pdf_link(): void
    {
        $this->companyUser('FR');
        $url = WhatsAppShare::url($this->facture('FAC-WA4'));
        $this->assertStringStartsWith('https://wa.me/33612345678?text=', $url);
        $text = rawurldecode(substr($url, strpos($url, '?text=') + 6));
        $this->assertStringContainsString('FAC-WA4', $text);
        $this->assertStringContainsString('/d/facture/', $text);
        $this->assertStringContainsString('signature=', $text);
    }

    public function test_without_phone_whatsapp_lets_the_user_pick_the_contact(): void
    {
        $this->companyUser('MA');
        $c = \App\Models\Client::create(['name' => 'Sans tel']);
        $d = Devis::create(['client_id' => $c->id, 'numero' => 'DEV-WA1', 'date' => now(), 'status' => 'draft', 'tva_rate' => 20, 'remise_type' => 'percent', 'remise_value' => 0]);
        $this->assertStringStartsWith('https://wa.me/?text=', WhatsAppShare::url($d->fresh(['client'])));
    }
}
