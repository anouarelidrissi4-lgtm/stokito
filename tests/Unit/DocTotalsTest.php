<?php

namespace Tests\Unit;

use App\Support\DocTotals;
use PHPUnit\Framework\TestCase;

class DocTotalsTest extends TestCase
{
    public function test_single_rate(): void
    {
        $t = DocTotals::compute([[100, 20], [50, 20]], 0);
        $this->assertSame(150.0, $t['ht']);
        $this->assertSame(30.0, $t['tva']);
        $this->assertSame(180.0, $t['ttc']);
        $this->assertCount(1, $t['groups']);
    }

    public function test_two_rates_with_global_discount_spread_pro_rata(): void
    {
        // lines 180 @20 % and 150 @5.5 %, 5 % global discount = 16.50
        $t = DocTotals::compute([[180, 20], [150, 5.5]], 16.5);
        $this->assertSame(313.5, $t['ht']);
        $this->assertSame([['rate' => 20.0, 'base' => 171.0, 'tva' => 34.2], ['rate' => 5.5, 'base' => 142.5, 'tva' => 7.84]], $t['groups']);
        $this->assertSame(355.54, $t['ttc']);
    }

    public function test_vat_is_rounded_once_per_rate(): void
    {
        // three lines of 0.10 @ 5.5 %: per line 0.0055 → 0.01 each would give 0.03; per rate gives 0.02
        $t = DocTotals::compute([[0.1, 5.5], [0.1, 5.5], [0.1, 5.5]], 0);
        $this->assertSame(0.02, $t['tva']);
    }

    public function test_zero_rate_and_empty_document(): void
    {
        $this->assertSame(100.0, DocTotals::compute([[100, 0]], 0)['ttc']);
        $e = DocTotals::compute([], 0);
        $this->assertSame(0.0, $e['ttc']);
        $this->assertSame([], $e['groups']);
    }

    public function test_labels(): void
    {
        $this->assertSame('5,5', DocTotals::label(5.5));
        $this->assertSame('20', DocTotals::label('20.00'));
        $this->assertSame('2,1', DocTotals::label(2.10));
    }
}
