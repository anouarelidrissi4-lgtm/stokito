<?php

namespace Tests\Feature;

use App\Models\Societe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PdfColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_colour_is_saved_and_used_by_the_pdf(): void
    {
        $this->companyUser('MA');
        $this->assertSame(['accent' => '#ffcc00', 'on' => '#111111', 'tint' => '#fff4c7'], Societe::current()->pdfColors());

        Livewire::test(\App\Livewire\Societe\Edit::class)->set('pdf_color', '#1E3A8A')->call('save')->assertHasNoErrors();
        Societe::flushCurrent();
        $c = Societe::current()->pdfColors();
        $this->assertSame('#1e3a8a', $c['accent']);
        $this->assertSame('#ffffff', $c['on']);             // white text on navy

        $f = \App\Models\Facture::create(['client_id' => $this->client('Martin')->id, 'numero' => 'FAC-00001', 'date' => now(), 'due_date' => now()->addDays(30),
            'tva_rate' => 20, 'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0, 'total_ht' => 100, 'total_ttc' => 120, 'montant_paye' => 0]);
        $html = view('pdf.document', ['societe' => Societe::current(), 'title' => 'Facture', 'numero' => $f->numero, 'date' => '26/11/2026', 'dueDate' => null,
            'status' => null, 'partyLabel' => 'Client', 'party' => $f->client, 'items' => collect(), 'showPrices' => true,
            'totals' => ['brut' => 100, 'remise' => 0, 'ht' => 100, 'tva_rate' => 20, 'groups' => null, 'ttc' => 120], 'notes' => null])->render();
        $this->assertStringContainsString('#1e3a8a', $html);
        $this->assertStringNotContainsString('#ffcc00', $html);
    }

    public function test_invalid_colour_is_refused(): void
    {
        $this->companyUser('FR');
        Livewire::test(\App\Livewire\Societe\Edit::class)->set('pdf_color', 'bleu')->call('save')->assertHasErrors('pdf_color');
    }
}
