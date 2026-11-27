<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerCodeBarreTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_page_is_public_and_indexed(): void
    {
        $this->get('/scanner-code-barre-en-ligne')->assertOk()
            ->assertSee('Scanner code barre en ligne')->assertSee('sc-app', false)->assertSee('sc-wedge', false)
            ->assertSee('/js/scanner-code-barre.js', false)->assertSee('FAQPage', false)->assertSee('@keyframes sc-scan', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('scanner-code-barre-en-ligne');
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Allow: /scanner-code-barre-en-ligne', $robots);
        $this->assertStringContainsString('Allow: /js/scanner-code-barre.js', $robots);
        $this->assertFileExists(public_path('js/zxing-0.21.3.min.js'));
        $this->get('/generateur-code-barre')->assertOk()->assertSee('scanner-code-barre-en-ligne');
        $this->get('/code-barre-pays')->assertOk()->assertSee('scanner-code-barre-en-ligne');
    }
}
