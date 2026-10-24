<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bot protection for the public contact and booking request forms.
 *
 * Works together with the "contact-forms" rate limiter (RouteServiceProvider)
 * and the hidden fields rendered by resources/views/site/partials/spam-guard.blade.php.
 */
class SpamGuard {

    const HONEYPOT = 'website';
    const STARTED = '_started';

    // Humans need at least this long to fill in a form.
    const MIN_SECONDS = 3;
    // A form older than this must be reloaded.
    const MAX_SECONDS = 86400;

    // Bots get a fake success so they do not adapt; humans get an error.
    const SILENT = ['honeypot', 'missing_timestamp', 'too_fast', 'links'];

    // Validation rule: no URLs or HTML in short text fields such as names.
    const NO_LINKS = 'not_regex:/(https?:|www\.|<|>|\[url)/i';

    public static function startedValue() {
        return Crypt::encryptString((string) time());
    }

    /**
     * Returns the reason the submission looks automated, or null when it is fine.
     */
    public static function check(Request $request, array $textFields = ['note']) {
        $reason = self::detect($request, $textFields);
        if ($reason !== null) {
            Log::notice('Form submission blocked', [
                'reason' => $reason,
                'ip' => $request->ip(),
                'route' => optional($request->route())->getName(),
                'agent' => substr((string) $request->userAgent(), 0, 200),
            ]);
        }
        return $reason;
    }

    public static function isSilent($reason) {
        return in_array($reason, self::SILENT, true);
    }

    private static function detect(Request $request, array $textFields) {
        if (trim((string) $request->input(self::HONEYPOT)) !== '') {
            return 'honeypot';
        }

        try {
            $started = (int) Crypt::decryptString((string) $request->input(self::STARTED));
        } catch (DecryptException $e) {
            return 'missing_timestamp';
        }
        $elapsed = time() - $started;
        if ($elapsed < self::MIN_SECONDS) {
            return 'too_fast';
        }
        if ($elapsed > self::MAX_SECONDS) {
            return 'expired';
        }

        foreach ($textFields as $field) {
            $text = (string) $request->input($field);
            if (preg_match_all('#https?://|www\.#i', $text) > 1 || preg_match('#<a\s|\[url|\[link#i', $text)) {
                return 'links';
            }
        }

        if (self::turnstileEnabled() && !self::verifyTurnstile($request)) {
            return 'captcha';
        }

        return null;
    }

    public static function turnstileEnabled() {
        return !empty(config('services.turnstile.site_key')) && !empty(config('services.turnstile.secret_key'));
    }

    private static function verifyTurnstile(Request $request) {
        $token = (string) $request->input('cf-turnstile-response');
        if ($token === '') {
            return false;
        }
        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
            return (bool) $response->json('success');
        } catch (\Throwable $e) {
            // Do not lose real requests when Cloudflare is unreachable; the
            // honeypot, time check and rate limit still apply.
            Log::warning('Turnstile verification unavailable: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Localized validation messages (only English ships with Laravel's own).
     */
    public static function messages() {
        return [
            'required' => __('site.form_required'),
            'email' => __('site.form_email'),
            'max' => __('site.form_too_long'),
            'date_format' => __('site.form_dates'),
            'after' => __('site.form_dates'),
            'after_or_equal' => __('site.form_date_past'),
            'integer' => __('site.form_guests'),
            'min' => __('site.form_guests'),
            'guests.max' => __('site.form_guests'),
            'not_regex' => __('site.form_no_links'),
        ];
    }

    /**
     * One-line cleanup for values placed in e-mail subjects and headers.
     */
    public static function singleLine($value, $limit = 80) {
        return mb_substr(trim(preg_replace('/[\r\n\t]+/', ' ', (string) $value)), 0, $limit);
    }

}
