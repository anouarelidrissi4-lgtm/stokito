<?php

namespace Tests\Feature;

use App\Livewire\Caisse\Pos;
use App\Models\CaisseCloture;
use App\Models\CaisseJournal;
use App\Models\CaisseTicket;
use App\Models\Societe;
use App\Support\Caisse;
use App\Support\CaisseException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CaisseTest extends TestCase
{
    use RefreshDatabase;

    protected function shop(array $user = []): void
    {
        $this->companyUser('FR', ['caisse_enabled' => true], $user);
    }

    public function test_a_sale_is_numbered_signed_and_takes_the_goods_out_of_stock(): void
    {
        $this->shop();
        $p = $this->product('VIS-1', 50, 5, 12);       // 12 € TTC, 20 %
        $q = $this->product('GANT', 10, 2, 4.9);

        $t1 = Caisse::encaisser([Caisse::lineFor($p, 2), Caisse::lineFor($q, 1)], ['especes' => 50]);
        $this->assertSame('T-000001', $t1->numero);
        $this->assertEquals(28.90, (float) $t1->total_ttc);
        $this->assertEquals(24.08, (float) $t1->total_ht);
        $this->assertEquals(4.82, (float) $t1->total_tva);
        $this->assertEquals(21.10, (float) $t1->rendu);
        $this->assertEquals(28.90, (float) $t1->paiements()->sum('montant')); // net cash = 50 - 21.10
        $this->assertEquals(48, (float) $p->fresh()->stock_qty);
        $this->assertSame(Caisse::GENESIS, $t1->prev_hash);

        $t2 = Caisse::encaisser([Caisse::lineFor($p, 1)], ['carte' => 12]);
        $this->assertSame('T-000002', $t2->numero);
        $this->assertSame($t1->hash, $t2->prev_hash);
        $this->assertTrue(Caisse::verifier()['ok']);
    }

    public function test_payment_rules(): void
    {
        $this->shop();
        $p = $this->product('A', 10, 5, 10);
        try {
            Caisse::encaisser([Caisse::lineFor($p)], ['especes' => 5]);
            $this->fail('insufficient payment accepted');
        } catch (CaisseException $e) {
            $this->assertStringContainsString('insuffisant', $e->getMessage());
        }
        try {
            Caisse::encaisser([Caisse::lineFor($p)], ['carte' => 20]);
            $this->fail('change on card accepted');
        } catch (CaisseException $e) {
            $this->assertStringContainsString('espèces', $e->getMessage());
        }
        $t = Caisse::encaisser([Caisse::lineFor($p)], ['carte' => 4, 'especes' => 10]);
        $this->assertEquals(4.0, (float) $t->rendu);
        $this->assertEqualsCanonicalizing(['carte' => 4.0, 'especes' => 6.0], $t->paiements->mapWithKeys(fn ($x) => [$x->mode => (float) $x->montant])->all());
        $this->assertSame(0, CaisseTicket::where('total_ttc', '<=', 0)->count());
    }

    public function test_a_recorded_ticket_can_never_be_changed_or_deleted(): void
    {
        $this->shop();
        $t = Caisse::encaisser([Caisse::lineFor($this->product('A'))], ['especes' => 20]);

        try {
            $t->update(['total_ttc' => 1]);
            $this->fail('model update allowed');
        } catch (\LogicException) {
        }
        try {
            DB::table('caisse_tickets')->where('id', $t->id)->update(['total_ttc' => 1]);
            $this->fail('raw update allowed');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('ne peuvent pas être modifiées', $e->getMessage());
        }
        try {
            DB::table('caisse_ticket_lignes')->where('ticket_id', $t->id)->delete();
            $this->fail('raw delete allowed');
        } catch (\Illuminate\Database\QueryException) {
        }
        $this->assertEquals(12, (float) $t->fresh()->total_ttc);
    }

    public function test_tampering_is_detected_by_the_integrity_check(): void
    {
        $this->shop();
        $p = $this->product('A');
        Caisse::encaisser([Caisse::lineFor($p)], ['especes' => 12]);
        $t = Caisse::encaisser([Caisse::lineFor($p, 3)], ['especes' => 36]);
        $this->assertTrue(Caisse::verifier()['ok']);

        DB::unprepared('DROP TRIGGER caisse_tickets_no_update');  // simulate someone bypassing the database protection
        DB::table('caisse_tickets')->where('id', $t->id)->update(['total_ttc' => 1]);
        $check = Caisse::verifier();
        $this->assertFalse($check['ok']);
        $this->assertStringContainsString($t->numero, implode(' ', $check['errors']));
    }

    public function test_signatures_do_not_depend_on_json_key_order(): void
    {
        // MySQL JSON columns give object keys back in their own order (found live 2026-11-25)
        $base = ['sequence' => 3, 'code' => 'IMPRESSION', 'libelle' => 'x', 'user_id' => 1, 'prev_hash' => Caisse::GENESIS, 'created_at' => '2026-11-25 10:00:00'];
        $a = new CaisseJournal($base + ['data' => ['ticket_id' => 6, 'n' => 0]]);
        $b = new CaisseJournal($base + ['data' => ['n' => 0, 'ticket_id' => 6]]);
        $this->assertSame(Caisse::sign(Caisse::journalPayload($a)), Caisse::sign(Caisse::journalPayload($b)));

        $z = ['type' => 'jour', 'sequence' => 1, 'periode' => '2026-11-25', 'first_seq' => 1, 'last_seq' => 2, 'nb_tickets' => 2, 'nb_annulations' => 0,
            'total_ht' => 10, 'total_tva' => 2, 'total_ttc' => 12, 'cumul_ttc' => 12, 'cumul_abs_ttc' => 12, 'user_id' => 1, 'prev_hash' => Caisse::GENESIS, 'created_at' => '2026-11-25 10:00:00'];
        $c1 = new CaisseCloture($z + ['tva_detail' => [['taux' => 20, 'ht' => 10, 'tva' => 2, 'ttc' => 12]], 'paiements' => ['especes' => 5, 'carte' => 7]]);
        $c2 = new CaisseCloture($z + ['tva_detail' => [['ttc' => 12, 'tva' => 2, 'ht' => 10, 'taux' => 20]], 'paiements' => ['carte' => 7, 'especes' => 5]]);
        $this->assertSame(Caisse::sign(Caisse::cloturePayload($c1)), Caisse::sign(Caisse::cloturePayload($c2)));
    }

    public function test_cancellation_reverses_stock_and_payments_once(): void
    {
        $this->shop();
        $p = $this->product('A', 10);
        $t = Caisse::encaisser([Caisse::lineFor($p, 2)], ['carte' => 24]);
        $this->assertEquals(8, (float) $p->fresh()->stock_qty);

        $a = Caisse::annuler($t, 'Erreur de saisie');
        $this->assertSame('annulation', $a->type);
        $this->assertSame($t->id, $a->annule_ticket_id);
        $this->assertEquals(-24, (float) $a->total_ttc);
        $this->assertEquals(-24, (float) $a->paiements()->sum('montant'));
        $this->assertEquals(10, (float) $p->fresh()->stock_qty);
        $this->assertTrue(CaisseJournal::where('code', 'ANNULATION')->exists());

        $this->expectException(CaisseException::class);
        Caisse::annuler($t->fresh(), 'encore');
    }

    public function test_daily_closure_freezes_totals_and_keeps_the_perpetual_grand_total(): void
    {
        $this->shop();
        $p = $this->product('A', 100);
        Caisse::encaisser([Caisse::lineFor($p, 1)], ['especes' => 12]);
        $t = Caisse::encaisser([Caisse::lineFor($p, 2)], ['carte' => 24]);
        Caisse::annuler($t, 'retour');

        $z1 = Caisse::cloturerJour();
        $this->assertSame(1, $z1->sequence);
        $this->assertSame(2, $z1->nb_tickets);
        $this->assertSame(1, $z1->nb_annulations);
        $this->assertEquals(12, (float) $z1->total_ttc);
        $this->assertEquals(12, (float) $z1->cumul_ttc);
        $this->assertEquals(60, (float) $z1->cumul_abs_ttc);   // 12 + 24 + |−24|
        $this->assertEquals(['especes' => 12.0, 'carte' => 0.0], array_map('floatval', $z1->paiements));

        try {
            Caisse::cloturerJour();
            $this->fail('empty closure accepted');
        } catch (CaisseException) {
        }
        Caisse::encaisser([Caisse::lineFor($p, 1)], ['especes' => 12]);
        $z2 = Caisse::cloturerJour();
        $this->assertEquals(24, (float) $z2->cumul_ttc);
        $this->assertSame($z1->hash, $z2->prev_hash);

        $month = Caisse::cloturerPeriode('mois', now()->format('Y-m'));
        $this->assertEquals(24, (float) $month->total_ttc);
        $this->assertSame(3, $month->nb_tickets);
        $this->assertTrue(Caisse::verifier()['ok']);
    }

    public function test_duplicates_are_marked_and_journaled(): void
    {
        $this->shop();
        $t = Caisse::encaisser([Caisse::lineFor($this->product('A'))], ['especes' => 12]);
        $this->get(route('caisse.tickets.print', $t->id))->assertOk()->assertDontSee('DUPLICATA');
        $this->get(route('caisse.tickets.print', $t->id))->assertOk()->assertSee('DUPLICATA N° 1');
        $this->assertSame(['IMPRESSION', 'DUPLICATA'], CaisseJournal::orderBy('sequence')->pluck('code')->all());
    }

    public function test_pos_screen_sells_and_respects_the_switch_and_permissions(): void
    {
        $this->companyUser('FR');
        $this->get(route('caisse.pos'))->assertForbidden();   // caisse not enabled
        Societe::current()->update(['caisse_enabled' => true]);
        Societe::flushCurrent();

        $p = $this->product('CODE-1', 5, 2, 9.5);
        Livewire::test(Pos::class)
            ->call('addByCode', 'CODE-1')
            ->call('inc', 0)
            ->call('openPay')
            ->call('exact', 'especes')
            ->call('encaisser')
            ->assertHasNoErrors()
            ->assertSet('cart', []);
        $this->assertEquals(19, (float) CaisseTicket::sole()->total_ttc);
        $this->assertEquals(3, (float) $p->fresh()->stock_qty);

        $this->companyUser('FR', ['caisse_enabled' => true], ['role' => 'user', 'permissions' => ['dashboard']]);
        $this->get(route('caisse.pos'))->assertForbidden();
    }

    public function test_signed_archive_of_a_closure_can_be_checked_and_detects_changes(): void
    {
        $this->shop();
        $p = $this->product('ARC-1', 50);
        Caisse::encaisser([Caisse::lineFor($p, 2)], ['especes' => 30]);
        $t = Caisse::encaisser([Caisse::lineFor($p, 1)], ['carte' => 12]);
        Caisse::annuler($t, 'test');
        $z = Caisse::cloturerJour();

        $this->get(route('caisse.clotures.archive', $z->id))->assertOk()->assertDownload();
        $path = Caisse::archive($z);
        $zip = new \ZipArchive();
        $zip->open($path);
        $this->assertSame(['tickets.csv', 'lignes.csv', 'paiements.csv', 'clotures.csv', 'journal.csv', 'LISEZMOI.txt', 'manifest.json'],
            collect(range(0, $zip->numFiles - 1))->map(fn ($i) => $zip->getNameIndex($i))->all());
        $this->assertSame(3, substr_count(trim($zip->getFromName('tickets.csv')), "\n"));   // header + 3 tickets
        $zip->close();
        $this->assertTrue(Caisse::verifierArchive($path)['ok']);

        $zip->open($path);
        $zip->addFromString('tickets.csv', str_replace('30.00', '3.00', $zip->getFromName('tickets.csv')));
        $zip->close();
        $check = Caisse::verifierArchive($path);
        $this->assertFalse($check['ok']);
        $this->assertStringContainsString('tickets.csv', implode(' ', $check['errors']));
        $this->assertSame(2, CaisseJournal::where('code', 'EXPORT_ARCHIVE')->count());
        @unlink($path);
    }

    public function test_switching_the_caisse_on_is_journaled(): void
    {
        $this->companyUser('FR');
        Livewire::test(\App\Livewire\Societe\Edit::class)->set('caisse_enabled', true)->call('save')->assertHasNoErrors();
        $this->assertTrue((bool) Societe::current()->fresh()->caisse_enabled);
        $this->assertSame('ACTIVATION', CaisseJournal::sole()->code);
    }

    public function test_counter_sales_count_in_the_dashboard(): void
    {
        $this->shop();
        $p = $this->product('DASH-1', 10, 5, 120);   // 120 € TTC = 100 HT, cost 5
        Caisse::encaisser([Caisse::lineFor($p, 2)], ['carte' => 240]);

        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(\App\Support\Fmt::money(240), $html);   // CA du mois + encaissé
        $this->assertStringContainsString(\App\Support\Fmt::money(190), $html);   // marge HT: 200 - 2 x 5
        $this->assertStringContainsString('Produit DASH-1', $html);              // top produits
    }

    public function test_companies_have_their_own_numbering_and_cannot_see_each_other(): void
    {
        $this->shop();
        $a = Caisse::encaisser([Caisse::lineFor($this->product('A'))], ['especes' => 12]);
        $this->shop();
        $b = Caisse::encaisser([Caisse::lineFor($this->product('B'))], ['especes' => 12]);
        $this->assertSame('T-000001', $b->numero);
        $this->assertNull(CaisseTicket::find($a->id));
        $this->get(route('caisse.tickets.print', $a->id))->assertNotFound();
    }

    public function test_demo_rows_stay_deletable_for_the_nightly_reset(): void
    {
        $this->shop(['is_demo' => true]);
        $t = Caisse::encaisser([Caisse::lineFor($this->product('A'))], ['especes' => 12]);
        DB::table('caisse_paiements')->where('ticket_id', $t->id)->delete();
        DB::table('caisse_ticket_lignes')->where('ticket_id', $t->id)->delete();
        DB::table('caisse_tickets')->where('id', $t->id)->delete();
        $this->assertSame(0, DB::table('caisse_tickets')->count());
    }

    public function test_automatic_closure_closes_each_past_day(): void
    {
        $this->shop();
        $p = $this->product('A', 100);
        $this->travelTo(now()->subDays(2)->setTime(10, 0));
        Caisse::encaisser([Caisse::lineFor($p)], ['especes' => 12]);
        $this->travelTo(now()->addDay());
        Caisse::encaisser([Caisse::lineFor($p, 2)], ['especes' => 24]);
        $this->travelBack();
        Caisse::encaisser([Caisse::lineFor($p)], ['especes' => 12]); // today: stays open

        $this->artisan('caisse:cloturer')->assertSuccessful();
        $this->actingAs(\App\Models\User::first());
        Societe::flushCurrent();
        $days = CaisseCloture::where('type', 'jour')->orderBy('sequence')->get();
        $this->assertSame([now()->subDays(2)->toDateString(), now()->subDay()->toDateString()], $days->pluck('periode')->all());
        $this->assertEquals([12, 24], $days->pluck('total_ttc')->map(fn ($v) => (float) $v)->all());
        $this->assertCount(1, Caisse::nonClotures());
    }
}
