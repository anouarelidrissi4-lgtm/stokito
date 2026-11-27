<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeBarrePaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_page_lists_the_prefixes(): void
    {
        $this->get('/code-barre-pays')->assertOk()
            ->assertSee('Code barre : ', false)->assertSee('300 à 379')->assertSee('Maroc')->assertSee('Cameroun')
            ->assertSee('FAQPage', false)->assertSee('chiffre-6', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('code-barre-pays');
        $this->assertStringContainsString('Allow: /code-barre-pays', file_get_contents(public_path('robots.txt')));
    }
}
