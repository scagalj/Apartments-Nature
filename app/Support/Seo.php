<?php

namespace App\Support;

use App\Models\Apartment;
use App\Models\ApartmentReviews;
use Illuminate\Support\Facades\Route;

/**
 * Helpers shared by the public pages: localized alternate URLs (hreflang),
 * Open Graph locales and the schema.org data describing the property.
 */
class Seo {

    public static function locales() {
        return config('app.locales');
    }

    /**
     * URL of the current page in every supported locale, keyed by locale.
     * Only routes that take a {locale} parameter have alternates.
     */
    public static function alternates() {
        $route = Route::current();
        if ($route === null || !in_array('locale', $route->parameterNames())) {
            return [];
        }

        $urls = [];
        foreach (self::locales() as $locale) {
            $urls[$locale] = route($route->getName(), array_merge($route->parameters(), ['locale' => $locale]));
        }
        return $urls;
    }

    public static function canonical() {
        $alternates = self::alternates();
        return $alternates[app()->getLocale()] ?? url()->current();
    }

    /**
     * Open Graph locale, e.g. "de_DE".
     */
    public static function regional($locale) {
        return config('laravellocalization.supportedLocales.' . $locale . '.regional', $locale);
    }

    public static function nativeName($locale) {
        return config('laravellocalization.supportedLocales.' . $locale . '.native', $locale);
    }

    public static function lodgingId() {
        return url('/') . '/#lodging';
    }

    public static function fullAddress() {
        $address = config('site.address');
        return $address['street'] . ', ' . $address['postal_code'] . ' ' . $address['city'] . ', ' . $address['country'];
    }

    /**
     * Lowest nightly price across all apartments, used for "from € X".
     */
    public static function startingPrice() {
        return Apartment::all()->map(fn($apartment) => $apartment->getStartingPrice())->filter()->min();
    }

    /**
     * schema.org LodgingBusiness describing the whole property.
     */
    public static function lodgingBusiness($lang) {
        $site = config('site');
        $summary = ApartmentReviews::summary();
        $startingPrice = self::startingPrice();

        $data = [
            '@type' => 'LodgingBusiness',
            '@id' => self::lodgingId(),
            'name' => $site['name'],
            'description' => __('site.meta_home_description'),
            'url' => route('welcome.locale', ['locale' => $lang]),
            'image' => [
                asset('images/selfcheckin/apartment-house.jpg'),
                asset('images/headers/main-header.jpg'),
                asset('images/apartments/6/1684094245.jpg'),
            ],
            'logo' => asset('images/apnaturelogo.png'),
            'telephone' => $site['phone'],
            'email' => $site['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address']['street'],
                'postalCode' => $site['address']['postal_code'],
                'addressLocality' => $site['address']['city'],
                'addressRegion' => $site['address']['region'],
                'addressCountry' => $site['address']['country_code'],
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $site['geo']['lat'],
                'longitude' => $site['geo']['lng'],
            ],
            'hasMap' => $site['google_maps_url'],
            'checkinTime' => $site['check_in']['from'],
            'checkoutTime' => $site['check_out'],
            'numberOfRooms' => Apartment::count(),
            'smokingAllowed' => false,
            'currenciesAccepted' => 'EUR',
            'amenityFeature' => array_map(fn($name) => [
                '@type' => 'LocationFeatureSpecification',
                'name' => $name,
                'value' => true,
            ], ['Free private parking', 'Free Wi-Fi', 'Air conditioning', 'Kitchenette', 'Balcony', 'Self check-in']),
        ];

        if ($startingPrice) {
            $data['priceRange'] = 'From €' . number_format($startingPrice, 0) . ' per night';
        }

        if (!empty($site['profiles'])) {
            $data['sameAs'] = array_values($site['profiles']);
        }

        if ($summary['count'] > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $summary['average'],
                'bestRating' => 10,
                'worstRating' => 1,
                'reviewCount' => $summary['count'],
            ];
            $data['review'] = ApartmentReviews::latestRated(5)->map(fn($review) => array_filter([
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $review->firstName],
                'datePublished' => $review->reviewDate ? substr($review->reviewDate, 0, 10) : null,
                'name' => $review->title,
                'reviewBody' => $review->positiveComment,
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (float) $review->ratingNumber,
                    'bestRating' => 10,
                    'worstRating' => 1,
                ],
            ]))->values()->all();
        }

        return $data;
    }

    public static function website($lang) {
        return [
            '@type' => 'WebSite',
            '@id' => url('/') . '/#website',
            'url' => url('/'),
            'name' => config('site.name'),
            'inLanguage' => $lang,
            'publisher' => ['@id' => self::lodgingId()],
        ];
    }

    public static function breadcrumbs(array $items) {
        $list = [];
        foreach (array_values($items) as $index => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ];
        }
        return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    /**
     * Wraps schema.org nodes in a single JSON-LD graph.
     */
    public static function jsonLd(array $nodes) {
        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
        );
    }

    /**
     * Shortens text to a meta-description friendly length on a word boundary.
     */
    public static function excerpt($text, $length = 158) {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $text))));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        $cut = mb_substr($text, 0, $length);
        return rtrim(mb_substr($cut, 0, mb_strrpos($cut, ' ')), ' ,.;:') . '…';
    }

    /**
     * Translation suffix for a review score out of 10 (site.score_word_*).
     */
    public static function scoreWord($score) {
        if ($score >= 9) {
            return 'exceptional';
        }
        if ($score >= 8) {
            return 'very_good';
        }
        if ($score >= 7) {
            return 'good';
        }
        return 'pleasant';
    }

    /**
     * "+385915933575" -> "+385 91 593 3575"
     */
    public static function formatPhone($phone) {
        if (preg_match('/^\+385(\d{2})(\d{3})(\d{3,4})$/', $phone, $m)) {
            return '+385 ' . $m[1] . ' ' . $m[2] . ' ' . $m[3];
        }
        return $phone;
    }

    /**
     * Emoji flag for an ISO 3166-1 alpha-2 country code.
     */
    public static function flag($countryCode) {
        $code = strtoupper(trim((string) $countryCode));
        if (!preg_match('/^[A-Z]{2}$/', $code)) {
            return '';
        }
        return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
    }

}
