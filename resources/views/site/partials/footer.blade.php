@php
    $lang = app()->getLocale();
    $home = route('welcome.locale', ['locale' => $lang]);
    $site = config('site');
@endphp
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="{{ $home }}">
                <span class="brand-mark">@include('site.partials.icon', ['name' => 'leaf'])</span>
                <span class="brand-text">
                    <span class="brand-name">{{ $site['name'] }}</span>
                    <span class="brand-place">Omiš · {{ __('site.croatia') }}</span>
                </span>
            </a>
            <p>{{ __('site.footer_tagline') }}</p>
            @if(!empty($site['profiles']))
            <div class="footer-profiles">
                <span>{{ __('site.find_us_on') }}</span>
                @foreach($site['profiles'] as $label => $url)
                <a href="{{ $url }}" rel="noopener" target="_blank">{{ $label }}</a>
                @endforeach
            </div>
            @endif
        </div>

        <nav class="footer-col" aria-label="{{ __('site.footer_explore') }}">
            <h2>{{ __('site.footer_explore') }}</h2>
            <ul>
                <li><a href="{{ $home }}#apartments">{{ __('messages.Apartments') }}</a></li>
                <li><a href="{{ $home }}#location">{{ __('messages.Location') }}</a></li>
                <li><a href="{{ $home }}#reviews">{{ __('messages.Reviews') }}</a></li>
                <li><a href="{{ $home }}#things-to-do">{{ __('site.nav_things_to_do') }}</a></li>
                <li><a href="{{ $home }}#faq">{{ __('site.nav_faq') }}</a></li>
                <li><a href="{{ route('selfcheckin.locale', ['locale' => $lang]) }}">{{ __('site.arrival_guide') }}</a></li>
                <li><a href="{{ route('privacypolicy', ['locale' => $lang]) }}">{{ __('messages.PrivacyPolicy') }}</a></li>
            </ul>
        </nav>

        <div class="footer-col">
            <h2>{{ __('messages.Contact') }}</h2>
            <address class="footer-contact">
                <p>@include('site.partials.icon', ['name' => 'map-pin'])<span>{{ $site['address']['street'] }}<br>{{ $site['address']['postal_code'] }} {{ $site['address']['city'] }}, {{ __('site.croatia') }}</span></p>
                <p>@include('site.partials.icon', ['name' => 'phone'])<a href="tel:{{ $site['phone'] }}">{{ \App\Support\Seo::formatPhone($site['phone']) }}</a></p>
                <p>@include('site.partials.icon', ['name' => 'whatsapp'])<a href="https://wa.me/{{ ltrim($site['whatsapp'], '+') }}" rel="noopener" target="_blank">WhatsApp {{ \App\Support\Seo::formatPhone($site['whatsapp']) }}</a></p>
                <p>@include('site.partials.icon', ['name' => 'mail'])<a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
            </address>
        </div>

        <nav class="footer-col" aria-label="{{ __('site.language') }}">
            <h2>{{ __('site.language') }}</h2>
            <ul class="footer-langs">
                @foreach(\App\Support\Seo::alternates() ?: array_combine(config('app.locales'), array_map(fn($l) => route('welcome.locale', ['locale' => $l]), config('app.locales'))) as $locale => $url)
                <li><a href="{{ $url }}" hreflang="{{ $locale }}" lang="{{ $locale }}">{{ \App\Support\Seo::nativeName($locale) }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>
    <div class="container footer-bottom">
        <span>© {{ date('Y') }} {{ $site['name'] }} · {{ \App\Support\Seo::fullAddress() }}</span>
    </div>
</footer>
