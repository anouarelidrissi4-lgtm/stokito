<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtiquetteCodeBarreTest extends TestCase
{
    use RefreshDatabase;

    public function test_label_page_is_public_and_indexed(): void
    {
        $this->get('/etiquette-code-barre')->assertOk()
            ->assertSee('Étiquette code barre')->assertSee('et-app', false)->assertSee('roll:50x30', false)
            ->assertSee('/js/etiquette-code-barre.js', false)->assertSee('FAQPage', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('etiquette-code-barre');
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Allow: /etiquette-code-barre', $robots);
        $this->assertStringContainsString('Allow: /js/etiquette-code-barre.js', $robots);
        $this->get('/generateur-code-barre')->assertOk()->assertSee('etiquette-code-barre');
    }
}
