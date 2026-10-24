<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Support\Facades\Response;

/**
 * XML sitemap with hreflang alternates and image entries.
 *
 * Only indexable pages are listed (home, contact and apartment pages in every
 * language). The privacy policy and self check-in guide are noindex.
 */
class SitemapController extends Controller {

    public function index() {
        $locales = config('app.locales');
        $apartments = Apartment::with('images')->get();
        $lastChange = optional($apartments->max('updated_at'))->toAtomString();

        $pages = [];
        $pages[] = ['route' => 'welcome.locale', 'params' => [], 'lastmod' => $lastChange, 'images' => $this->homeImages()];
        $pages[] = ['route' => 'contactus', 'params' => [], 'lastmod' => null, 'images' => []];
        foreach ($apartments as $apartment) {
            $pages[] = [
                'route' => 'apartment.locale',
                'params' => ['apartment' => $apartment->id],
                'lastmod' => optional($apartment->updated_at)->toAtomString(),
                'images' => $apartment->getAllImages()->map(fn($image) => url($apartment->imageUrl($image)))->all(),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($pages as $page) {
            $urls = [];
            foreach ($locales as $locale) {
                $urls[$locale] = route($page['route'], array_merge($page['params'], ['locale' => $locale]));
            }

            foreach ($urls as $url) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . e($url) . "</loc>\n";
                if ($page['lastmod']) {
                    $xml .= '    <lastmod>' . $page['lastmod'] . "</lastmod>\n";
                }
                foreach ($urls as $alternateLocale => $alternateUrl) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $alternateLocale . '" href="' . e($alternateUrl) . '"/>' . "\n";
                }
                if ($page['route'] === 'welcome.locale') {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e(url('/')) . '"/>' . "\n";
                }
                foreach ($page['images'] as $image) {
                    $xml .= '    <image:image><image:loc>' . e($image) . "</image:loc></image:image>\n";
                }
                $xml .= "  </url>\n";
            }
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function homeImages() {
        return [
            asset('images/site/hero-house.jpg'),
            asset('images/site/hero-terrace.jpg'),
            asset('images/site/hero-balcony.jpg'),
        ];
    }

}
