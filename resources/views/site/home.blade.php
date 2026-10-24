@extends('site.layout')
@use('App\Support\Seo')

@php
    $site = config('site');
    $d = $site['distances'];
    $home = route('welcome.locale', ['locale' => $lang]);
    $maxGuests = max(1, $apartments->max(fn($apartment) => $apartment->getNumberOfGuests()) ?: 2);
    $startingPrice = Seo::startingPrice();

    $faqParams = [
        'beach' => $d['beach'], 'old_town' => $d['old_town'], 'airport' => $d['airport'], 'split' => $d['split'],
        'from' => $site['check_in']['from'], 'until' => $site['check_in']['until'], 'out' => $site['check_out'],
        'count' => $apartments->count(),
    ];
    $faqs = [];
    foreach (range(1, 8) as $i) {
        $faqs[] = [__('site.faq_q' . $i, $faqParams), __('site.faq_a' . $i, $faqParams)];
    }

    $highlights = [
        ['parking', 'highlight_parking'],
        ['key', 'highlight_checkin'],
        ['wifi', 'highlight_comfort'],
        ['tag', 'highlight_direct'],
    ];

    $distances = [
        ['waves', 'cetina'], ['town', 'old_town'], ['sun', 'beach'],
        ['car', 'split'], ['plane', 'airport'], ['mountain', 'makarska'],
    ];

    $activities = [
        ['rafting.jpg', 'RaftingOnCetina'], ['zipline.jpg', 'ZipLine'],
        ['ferrata-omis.jpg', 'FerataOmis'], ['fortress-fortica.jpg', 'FortressFortica'],
        ['pirate-battle.jpg', 'PirateBattle'], ['rock-climbing.jpg', 'RockClimbing'],
        ['mountain-bike.jpg', 'MountainBike'], ['traditional-dishes.jpg', 'TraditionalDishes'],
    ];

    $schema = [
        Seo::lodgingBusiness($lang),
        [
            '@type' => 'ItemList',
            'name' => __('site.apartments_title'),
            'itemListElement' => $apartments->values()->map(fn($apartment, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => route('apartment.locale', ['locale' => $lang, 'apartment' => $apartment->id]),
                'name' => $apartment->displayName(),
            ])->all(),
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn($faq) => [
                '@type' => 'Question',
                'name' => $faq[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
            ], $faqs),
        ],
    ];
@endphp

@section('title', __('site.meta_home_title'))
@section('description', __('site.meta_home_description'))
@section('body_class', 'page-home')

@push('head')
    @include('site.partials.datepicker-assets')
@endpush

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">@include('site.partials.icon', ['name' => 'map-pin']) {{ __('site.hero_eyebrow') }}</p>
            <h1>{{ __('site.hero_title') }}</h1>
            <p class="hero-lead">{{ __('site.hero_lead', ['count' => $apartments->count()]) }}</p>

            @if($reviewSummary['count'] > 0)
            <a class="hero-rating" href="#reviews">
                <span class="hero-rating-score">{{ number_format($reviewSummary['average'], 1) }}</span>
                <span class="hero-rating-text">
                    <strong>{{ __('site.score_word_' . Seo::scoreWord($reviewSummary['average'])) }}</strong>
                    {{ trans_choice('site.reviews_count', $reviewSummary['count'], ['count' => $reviewSummary['count']]) }}
                </span>
                <span class="stars" aria-hidden="true">@for($i = 0; $i < 5; $i++)@include('site.partials.icon', ['name' => 'star'])@endfor</span>
            </a>
            @endif

            <form class="search-card" action="{{ route('apartment.searchCriteria.update') }}" method="post" data-date-range>
                @csrf
                <div class="field">
                    <label for="checkIn">{{ __('messages.CheckIn') }}</label>
                    <input id="checkIn" name="checkIn" type="text" inputmode="none" autocomplete="off" placeholder="dd.mm.yyyy" value="{{ Session::get('checkIn') }}" required data-check-in>
                </div>
                <div class="field">
                    <label for="checkOut">{{ __('messages.CheckOut') }}</label>
                    <input id="checkOut" name="checkOut" type="text" inputmode="none" autocomplete="off" placeholder="dd.mm.yyyy" value="{{ Session::get('checkOut') }}" required data-check-out>
                </div>
                <div class="field field-guests">
                    <label for="guests">{{ __('messages.Guests') }}</label>
                    <input id="guests" name="guests" type="number" min="1" max="{{ $maxGuests }}" value="{{ Session::get('guests') ?? 2 }}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-search">{{ __('site.check_prices') }}</button>
            </form>
            <p class="hero-note">@include('site.partials.icon', ['name' => 'check']) {{ __('site.hero_note') }}</p>
        </div>

        <div class="hero-media">
            <figure class="hero-img hero-img-main">
                <img src="{{ asset('images/site/hero-terrace.jpg') }}" alt="{{ __('site.hero_img_terrace') }}" width="519" height="768" fetchpriority="high">
            </figure>
            <figure class="hero-img hero-img-house">
                <img src="{{ asset('images/site/hero-house.jpg') }}" alt="{{ __('site.hero_img_house') }}" width="603" height="505">
                <figcaption>@include('site.partials.icon', ['name' => 'map-pin']) {{ $site['address']['street'] }}, Omiš</figcaption>
            </figure>
            <figure class="hero-img hero-img-balcony">
                <img src="{{ asset('images/site/hero-balcony.jpg') }}" alt="{{ __('site.hero_img_balcony') }}" width="1024" height="683">
            </figure>
        </div>
    </div>
</section>

{{-- HIGHLIGHTS --}}
<section class="highlights" aria-label="{{ __('site.highlights_label') }}">
    <div class="container">
        <div class="highlights-grid">
        @foreach($highlights as [$icon, $key])
        <div class="highlight">
            <span class="highlight-icon">@include('site.partials.icon', ['name' => $icon])</span>
            <div>
                <h2 class="highlight-title">{{ __('site.' . $key . '_title') }}</h2>
                <p>{{ __('site.' . $key . '_text', ['from' => $site['check_in']['from'], 'until' => $site['check_in']['until']]) }}</p>
            </div>
        </div>
        @endforeach
        </div>
    </div>
</section>

{{-- APARTMENTS --}}
<section class="section" id="apartments">
    <div class="container">
        <header class="section-head">
            <p class="eyebrow">{{ __('site.apartments_eyebrow') }}</p>
            <h2>{{ __('site.apartments_title') }}</h2>
            <p class="section-lead">{{ __('site.apartments_lead') }}</p>
            @if(Session::get('checkIn') && Session::get('checkOut'))
            <p class="dates-chip">@include('site.partials.icon', ['name' => 'calendar']) {{ Session::get('checkIn') }} – {{ Session::get('checkOut') }} · {{ Session::get('guests') }} {{ __('messages.Guests') }}</p>
            @endif
        </header>
        <div class="apt-grid">
            @foreach($apartments as $apartment)
                @include('site.partials.apartment-card', ['apartment' => $apartment])
            @endforeach
        </div>
    </div>
</section>

{{-- THE HOUSE --}}
<section class="section section-tinted" id="about">
    <div class="container split">
        <div class="split-media">
            <figure class="photo-frame">
                <img src="{{ asset('images/site/hero-house.jpg') }}" alt="{{ __('site.hero_img_house') }}" width="603" height="505" loading="lazy" decoding="async">
                <figcaption>{{ __('site.about_caption') }}</figcaption>
            </figure>
        </div>
        <div class="split-copy">
            <p class="eyebrow">{{ __('site.about_eyebrow') }}</p>
            <h2>{{ __('site.about_title') }}</h2>
            <p>{{ __('site.about_p1', ['count' => $apartments->count()]) }}</p>
            <p>{{ __('site.about_p2') }}</p>
            <ul class="check-list">
                @foreach(range(1, 5) as $i)
                <li>@include('site.partials.icon', ['name' => 'check']) {{ __('site.about_li' . $i) }}</li>
                @endforeach
            </ul>
            <address class="nap-card">
                <strong>{{ $site['name'] }}</strong>
                <span>{{ Seo::fullAddress() }}</span>
                <span><a href="tel:{{ $site['phone'] }}">{{ Seo::formatPhone($site['phone']) }}</a> · <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></span>
            </address>
        </div>
    </div>
</section>

{{-- REVIEWS --}}
@if($reviews->isNotEmpty())
<section class="section" id="reviews">
    <div class="container">
        <header class="section-head">
            <p class="eyebrow">{{ __('messages.GuestReviews') }}</p>
            <h2>{{ __('site.reviews_title') }}</h2>
            <p class="section-lead">{{ __('site.reviews_lead') }}</p>
        </header>
        @include('site.partials.reviews')
    </div>
</section>
@endif

{{-- LOCATION --}}
<section class="section section-tinted" id="location">
    <div class="container split split-wide">
        <div class="split-copy">
            <p class="eyebrow">{{ __('messages.Location') }}</p>
            <h2>{{ __('site.location_title') }}</h2>
            <p>{{ __('site.location_p1') }}</p>
            <p>{{ __('site.location_p2') }}</p>
            <ul class="distance-list">
                @foreach($distances as [$icon, $key])
                <li>
                    <span class="distance-icon">@include('site.partials.icon', ['name' => $icon])</span>
                    <span class="distance-name">{{ __('site.distance_' . $key) }}</span>
                    <span class="distance-value">{{ $d[$key] }}</span>
                </li>
                @endforeach
            </ul>
            <div class="btn-row">
                <a class="btn btn-primary" href="{{ $site['google_maps_url'] }}" target="_blank" rel="noopener">@include('site.partials.icon', ['name' => 'map-pin']) {{ __('site.get_directions') }}</a>
                <a class="btn btn-ghost" href="{{ route('selfcheckin.locale', ['locale' => $lang]) }}">{{ __('site.arrival_guide') }} @include('site.partials.icon', ['name' => 'arrow-right'])</a>
            </div>
        </div>
        <div class="map-frame">
            <iframe src="{{ $site['google_maps_embed'] }}" title="{{ __('site.map_title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    </div>
</section>

{{-- THINGS TO DO --}}
<section class="section" id="things-to-do">
    <div class="container">
        <header class="section-head">
            <p class="eyebrow">{{ __('messages.ExploreLikeALocal') }}</p>
            <h2>{{ __('site.explore_title') }}</h2>
            <p class="section-lead">{{ __('messages.ExploreLikeALocalDescription') }}</p>
        </header>
        <div class="activity-grid">
            @foreach($activities as [$image, $key])
            <article class="activity-card">
                <img src="{{ asset('images/explorelikelocal/' . $image) }}" alt="{{ __('messages.' . $key) }} – Omiš" width="360" height="250" loading="lazy" decoding="async">
                <div class="activity-body">
                    <h3>{{ __('messages.' . $key) }}</h3>
                    <p>{{ __('messages.' . $key . 'Text') }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="section section-tinted" id="faq">
    <div class="container faq-wrap">
        <header class="section-head">
            <p class="eyebrow">{{ __('site.nav_faq') }}</p>
            <h2>{{ __('site.faq_title') }}</h2>
        </header>
        <div class="faq-list">
            @foreach($faqs as [$question, $answer])
            <details class="faq-item" @if($loop->first) open @endif>
                <summary><h3>{{ $question }}</h3>@include('site.partials.icon', ['name' => 'chevron-down'])</summary>
                <p>{{ $answer }}</p>
            </details>
            @endforeach
        </div>
    </div>
</section>

@include('site.partials.cta')

@endsection
