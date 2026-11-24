<?php

namespace Tests\Unit;

use App\Support\Relance;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RelancePhoneTest extends TestCase
{
    public static function numbers(): array
    {
        return [
            'France mobile' => ['FR', '06 12 34 56 78', '33612345678'],
            'France international' => ['FR', '+33 6 12 34 56 78', '33612345678'],
            'France 0033' => ['FR', '0033 6 12 34 56 78', '33612345678'],
            'Maroc 0' => ['MA', '0612345678', '212612345678'],
            'Maroc 9 chiffres' => ['MA', '612345678', '212612345678'],
            'Maroc déjà international' => ['MA', '212612345678', '212612345678'],
            'Sénégal' => ['SN', '77 123 45 67', '221771234567'],
            'Belgique' => ['BE', '0470 12 34 56', '32470123456'],
            'trop court' => ['FR', '123', null],
            'vide' => ['FR', '', null],
        ];
    }

    #[DataProvider('numbers')]
    public function test_whatsapp_number(string $country, string $raw, ?string $expected): void
    {
        $this->assertSame($expected, Relance::phone($raw, $country));
    }
}
