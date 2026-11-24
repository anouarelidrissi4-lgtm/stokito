<?php

namespace Tests\Feature;

use App\Mail\DocumentMail;
use App\Models\Facture;
use App\Support\DocumentSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailAndRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function overdueInvoice(int $daysLate = 8): Facture
    {
        $p = $this->product('R'.$daysLate);
        $f = Facture::create(['client_id' => $this->client('Client Relance', 'client@example.test')->id, 'numero' => 'FAC-R'.$daysLate, 'date' => now()->subDays(30 + $daysLate),
            'due_date' => now()->subDays($daysLate), 'tva_rate' => 20, 'status' => 'envoyee', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $p->id, 'quantity' => 10, 'unit_price' => 10, 'tva_rate' => 20, 'total' => 100]);
        $f->recalculateTotals();

        return $f->fresh();
    }

    public function test_automatic_reminder_is_sent_once_per_level(): void
    {
        Mail::fake();
        $this->companyUser('FR', ['relances_auto' => true]);
        $f = $this->overdueInvoice(8);
        Auth::logout();

        $this->artisan('factures:relancer')->assertSuccessful();
        $this->artisan('factures:relancer')->assertSuccessful();

        Mail::assertSent(DocumentMail::class, 1);
        Mail::assertSent(DocumentMail::class, fn ($m) => $m->hasTo('client@example.test') && str_starts_with($m->sujet, 'Rappel'));
        $this->assertSame(1, (int) $f->fresh()->relance_niveau);
    }

    public function test_no_reminder_when_the_option_is_off_or_the_invoice_is_outside_the_window(): void
    {
        Mail::fake();
        $this->companyUser('FR', ['relances_auto' => false]);
        $this->overdueInvoice(8);
        $this->companyUser('FR', ['relances_auto' => true]);
        $this->overdueInvoice(20); // past the J+7 window, before the J+30 one: nothing
        Auth::logout();
        $this->artisan('factures:relancer')->assertSuccessful();
        Mail::assertNothingSent();
    }

    public function test_demo_account_cannot_send_emails(): void
    {
        Mail::fake();
        $this->companyUser('FR', [], ['is_demo' => true]);
        $f = $this->overdueInvoice();
        $this->assertNotNull(DocumentSender::send($f, 'client@example.test', 'x', 'y'));
        Mail::assertNothingSent();
    }

    public function test_sending_an_invoice_attaches_the_pdf_and_marks_it_sent(): void
    {
        Mail::fake();
        $this->companyUser();
        $f = Facture::create(['client_id' => $this->client('C', 'c@example.test')->id, 'numero' => 'FAC-E1', 'date' => now(), 'due_date' => now()->addDays(30), 'tva_rate' => 20, 'status' => 'brouillon', 'remise_type' => 'percent', 'remise_value' => 0]);
        $f->items()->create(['product_id' => $this->product('E1')->id, 'quantity' => 1, 'unit_price' => 10, 'tva_rate' => 20, 'total' => 10]);
        $f->recalculateTotals();

        $this->assertNull(DocumentSender::send($f->fresh(), 'c@example.test', 'Facture', 'Bonjour'));
        Mail::assertSent(DocumentMail::class, fn ($m) => $m->filename === 'FAC-E1.pdf' && str_starts_with((string) $m->pdf, '%PDF'));
        $this->assertSame('envoyee', $f->fresh()->status);
        $this->assertNotNull($f->fresh()->sent_at);
    }
}
