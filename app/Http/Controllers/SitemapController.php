<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Public sitemap.xml for Search Console and crawlers.
 *
 * Built as a raw XML string (not a Blade view) so `<?xml` cannot be
 * interpreted as a PHP short open tag — that 500s the URL and Google
 * reports "Couldn't fetch".
 *
 * Only indexable guest URLs are listed. Auth pages and /admin stay out.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            $this->url(route('home'), 'weekly', '1.0'),
            $this->url(route('courses'), 'daily', '0.9'),
            $this->url(route('calendar'), 'daily', '0.8'),
            $this->url(route('range'), 'weekly', '0.7'),
            $this->url(route('shop'), 'weekly', '0.6'),
            // Rifle Builder intentionally omitted — admin-only preview page.
            $this->url(route('contact.create'), 'monthly', '0.7'),
            $this->url(route('legal.index'), 'yearly', '0.4'),
            $this->url(route('legal.privacy'), 'yearly', '0.3'),
            $this->url(route('legal.terms'), 'yearly', '0.3'),
            $this->url(route('legal.shipping'), 'yearly', '0.3'),
            $this->url(route('legal.refunds'), 'yearly', '0.3'),
            $this->url(route('llms'), 'weekly', '0.4'),
            $this->url(route('llms.full'), 'weekly', '0.3'),
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.htmlspecialchars($url['loc'], ENT_XML1)."</loc>\n";
            $xml .= '        <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '        <priority>'.$url['priority']."</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * @return array{loc: string, changefreq: string, priority: string}
     */
    private function url(string $loc, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
