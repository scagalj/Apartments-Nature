@php
    $lang = app()->getLocale();
    $home = route('welcome.locale', ['locale' => $lang]);
    $languageLinks = \App\Support\Seo::alternates();
    $navItems = [
        [$home . '#apartments', __('messages.Apartments')],
        [$home . '#location', __('messages.Location')],
        [$home . '#reviews', __('messages.Reviews')],
        [$home . '#things-to-do', __('site.nav_things_to_do')],
        [$home . '#faq', __('site.nav_faq')],
        [route('contactus', ['locale' => $lang]), __('messages.Contact')],
    ];
@endphp
<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="{{ $home }}" aria-label="{{ config('site.name') }} – {{ __('messages.Home') }}">
            <span class="brand-mark">@include('site.partials.icon', ['name' => 'leaf'])</span>
            <span class="brand-text">
                <span class="brand-name">{{ config('site.name') }}</span>
                <span class="brand-place">Omiš · {{ __('site.croatia') }}</span>
            </span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="{{ __('site.menu') }}">
            <ul>
                @foreach($navItems as [$url, $label])
                <li><a href="{{ $url }}">{{ $label }}</a></li>
                @endforeach
            </ul>
            <a class="btn btn-primary main-nav-cta" href="{{ $home }}#apartments">{{ __('site.book_direct') }}</a>
        </nav>

        <div class="header-actions">
            <details class="lang-switch" data-lang-switch>
                <summary aria-label="{{ __('site.language') }}">
                    @include('site.partials.icon', ['name' => 'globe'])
                    <span>{{ strtoupper($lang) }}</span>
                </summary>
                <ul>
                    @foreach(config('app.locales') as $locale)
                    <li>
                        <a href="{{ $languageLinks[$locale] ?? route('welcome.locale', ['locale' => $locale]) }}" hreflang="{{ $locale }}" lang="{{ $locale }}" @if($locale === $lang) aria-current="true" @endif>
                            {{ \App\Support\Seo::nativeName($locale) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </details>
            <a class="btn btn-primary header-cta" href="{{ $home }}#apartments">{{ __('site.book_direct') }}</a>
            <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="{{ __('site.menu') }}" data-nav-toggle>
                @include('site.partials.icon', ['name' => 'menu', 'class' => 'icon-open'])
                @include('site.partials.icon', ['name' => 'x', 'class' => 'icon-close'])
            </button>
        </div>
    </div>
</header>
