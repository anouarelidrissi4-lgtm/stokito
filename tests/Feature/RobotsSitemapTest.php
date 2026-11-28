<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Every URL of the sitemap must be allowed by public/robots.txt (which disallows everything else) (2026-11-28). */
class RobotsSitemapTest extends TestCase
{
    public function test_every_sitemap_url_is_allowed_by_robots_txt(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        preg_match_all('/^Allow:\s*(\S+)/mi', $robots, $m);
        $allow = $m[1];
        $this->assertStringContainsString('Disallow: /', $robots);

        $xml = $this->get(route('sitemap'))->assertOk()->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $locs);
        $this->assertNotEmpty($locs[1]);
        foreach ($locs[1] as $loc) {
            $path = parse_url($loc, PHP_URL_PATH) ?: '/';
            $ok = false;
            foreach ($allow as $a) {
                $ok = $ok || (str_ends_with($a, '$') ? $path === rtrim($a, '$') : str_starts_with($path, $a));
            }
            $this->assertTrue($ok, "robots.txt blocks $path (listed in the sitemap)");
        }
    }
}
