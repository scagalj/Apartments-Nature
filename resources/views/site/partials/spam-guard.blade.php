{{-- Bot protection, checked by App\Support\SpamGuard. Include inside every public form. --}}
<div class="hp-field" aria-hidden="true">
    <label for="{{ $prefix ?? 'f' }}-website">Website</label>
    <input id="{{ $prefix ?? 'f' }}-website" type="text" name="{{ \App\Support\SpamGuard::HONEYPOT }}" value="" tabindex="-1" autocomplete="off">
</div>
<input type="hidden" name="{{ \App\Support\SpamGuard::STARTED }}" value="{{ \App\Support\SpamGuard::startedValue() }}">
@if(\App\Support\SpamGuard::turnstileEnabled())
<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="{{ app()->getLocale() }}" data-theme="light"></div>
@once
@push('scripts')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endpush
@endonce
@endif

@if($errors->any())
<div class="alert alert-error" role="alert">
    <strong>{{ __('site.form_error_title') }}</strong>
    <ul>
        @foreach(array_unique($errors->all()) as $message)
        <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
@endif
