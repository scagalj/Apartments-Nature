<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentReviews;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

/**
 * Public pages of the website.
 */
class SiteController extends BaseController {

    /**
     * The bare domain sends visitors to their language version: the one they
     * chose before, otherwise the browser language. Temporary (302) redirect,
     * as recommended by Google for locale-adaptive home pages.
     */
    public function root(Request $request) {
        $locale = session('locale') ?: $request->getPreferredLanguage(config('app.locales'));
        if (!in_array($locale, config('app.locales'))) {
            $locale = LangController::getDefaultLanguage();
        }
        return redirect()->route('welcome.locale', ['locale' => $locale], 302);
    }

    public function home($locale) {
        $lang = LangController::updateLanguage($locale);

        return view('site.home', [
            'lang' => $lang,
            'apartments' => self::orderedApartments(),
            'reviews' => ApartmentReviews::newestFirst(),
            'reviewSummary' => ApartmentReviews::summary(),
        ]);
    }

    public function contact($locale) {
        $lang = LangController::updateLanguage($locale);
        return view('site.contact', ['lang' => $lang]);
    }

    public function privacyPolicy($locale) {
        $lang = LangController::updateLanguage($locale);
        return view('site.privacy', ['lang' => $lang]);
    }

    public function selfCheckIn($locale) {
        $lang = LangController::updateLanguage($locale);
        return view('site.selfcheckin', ['lang' => $lang]);
    }

    /**
     * All apartments in the order configured in config/site.php.
     */
    public static function orderedApartments() {
        $order = array_flip(config('site.apartment_order', []));
        return Apartment::with('images')->get()
                ->sortBy(fn($apartment) => $order[$apartment->id] ?? 1000 + $apartment->id)
                ->values();
    }

}
