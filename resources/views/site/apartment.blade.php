@extends('site.layout')
@use('App\Support\Seo')

@php
    $site = config('site');
    $home = route('welcome.locale', ['locale' => $lang]);
    $pageUrl = route('apartment.locale', ['locale' => $lang, 'apartment' => $apartment->id]);
    $name = $apartment->displayName();
    $guestsCount = $apartment->getNumberOfGuests();
    $bedsCount = $apartment->getNumberOfBeds();
    $bathroomsCount = $apartment->getNumberOfBathrooms();
    $size = $apartment->sizeValue();
    $images = $apartment->getAllImages();
    $galleryJson = json_encode(
        $images->map(fn($image, $i) => ['src' => $apartment->imageUrl($image), 'alt' => $apartment->imageAlt($image, $i)])->values(),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
    );
    $descriptionHtml = $apartment->getdescriptionvalue($lang) ?: $apartment->getdescriptionvalue('en');
    $plainDescription = $apartment->plainDescription($lang);
    $accessories = $apartment->getAllAccessoriesWithPopularFirst() ?? collect();
    $house = $apartment->house;

    $metaDescription = $plainDescription
        ? Seo::excerpt(__('site.meta_apartment_prefix', ['apartment' => $name, 'size' => $size, 'guests' => $guestsCount]) . ' ' . $plainDescription)
        : __('site.meta_apartment_description', ['apartment' => $name, 'size' => $size, 'guests' => $guestsCount]);

    // Booking card price, from the dates the visitor already selected.
    $checkIn = Session::get('checkIn');
    $checkOut = Session::get('checkOut');
    $sessionGuests = Session::get('guests');
    $datesSelected = !empty($checkIn) && !empty($checkOut);
    $price = $apartment->calculatePrice($checkIn, $checkOut, $sessionGuests);
    $priceText = $apartment->getPriceValueTextForApartment($datesSelected, $price);
    $priceAmount = $apartment->getPriceAmountTextForApartment($datesSelected, $price);
    $startingPrice = $apartment->getStartingPrice();

    $houseRules = [];
    if ($house && $house->isHaveHouseRule()) {
        $houseRules[] = ['clock', __('messages.CheckInRule')];
        $houseRules[] = ['clock', __('messages.CheckOutRule')];
        if ($house->quietHoursRule) {
            $houseRules[] = ['sun', __('messages.QuitHoursRule')];
        }
        if ($house->extraBedPolicy) {
            $houseRules[] = ['bed', __('messages.ExtraBedRule')];
        }
        $houseRules[] = ['shield', $house->smokingAllowed ? __('messages.SmokingIsAllowd') : __('messages.SmokingIsNotAllowd')];
        $houseRules[] = ['users', $house->partiesAllowed ? __('messages.PartiesAreAllowed') : __('messages.PartiesAreNotAllowed')];
    }

    $schema = [
        array_filter([
            '@type' => 'Apartment',
            '@id' => $pageUrl . '#apartment',
            'name' => $name . ' – ' . $site['name'],
            'description' => $plainDescription ?: $metaDescription,
            'url' => $pageUrl,
            'image' => $images->map(fn($image) => url($apartment->imageUrl($image)))->values()->all(),
            'floorSize' => ['@type' => 'QuantitativeValue', 'value' => $size, 'unitCode' => 'MTK'],
            'numberOfRooms' => 1,
            'numberOfBathroomsTotal' => $bathroomsCount ?: null,
            'occupancy' => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => $guestsCount],
            'amenityFeature' => $accessories->map(fn($item) => [
                '@type' => 'LocationFeatureSpecification',
                'name' => $item->accessories->displayName(),
                'value' => true,
            ])->values()->all() ?: null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address']['street'],
                'postalCode' => $site['address']['postal_code'],
                'addressLocality' => $site['address']['city'],
                'addressCountry' => $site['address']['country_code'],
            ],
            'containedInPlace' => ['@id' => Seo::lodgingId()],
        ]),
        Seo::lodgingBusiness($lang),
        Seo::breadcrumbs([
            [__('messages.Home'), $home],
            [__('messages.Apartments'), $home . '#apartments'],
            [$name, $pageUrl],
        ]),
    ];
@endphp

@section('title', __('site.meta_apartment_title', ['apartment' => $name, 'guests' => $guestsCount]))
@section('description', $metaDescription)
@section('og_image', $images->isNotEmpty() ? url($apartment->imageUrl($images->first())) : '')
@section('body_class', 'page-apartment')

@push('head')
    @include('site.partials.datepicker-assets')
@endpush

@section('content')
<div class="container apt-page">

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ $home }}">{{ __('messages.Home') }}</a></li>
            <li><a href="{{ $home }}#apartments">{{ __('messages.Apartments') }}</a></li>
            <li aria-current="page">{{ $name }}</li>
        </ol>
    </nav>

    <header class="apt-header">
        <div>
            <h1>{{ $name }} <span class="apt-header-sub">· {{ __('site.in_omis') }}</span></h1>
            <p class="apt-header-meta">
                @if($reviewSummary['count'] > 0)
                <a href="#reviews" class="meta-rating">@include('site.partials.icon', ['name' => 'star']) {{ number_format($reviewSummary['average'], 1) }}/10 · {{ trans_choice('site.reviews_count', $reviewSummary['count'], ['count' => $reviewSummary['count']]) }}</a>
                <span aria-hidden="true">·</span>
                @endif
                <a href="#location">@include('site.partials.icon', ['name' => 'map-pin']) {{ Seo::fullAddress() }}</a>
            </p>
        </div>
    </header>

    {{-- GALLERY --}}
    @if($images->isNotEmpty())
    <div class="gallery gallery-count-{{ min($images->count(), 5) }}" data-gallery>
        @foreach($images->take(5) as $image)
        @php [$w, $h] = $image->dimensions(); @endphp
        <button type="button" class="gallery-item" data-gallery-open="{{ $loop->index }}" aria-label="{{ __('site.open_photo', ['number' => $loop->iteration]) }}">
            <img src="{{ $apartment->imageUrl($image) }}" alt="{{ $apartment->imageAlt($image, $loop->index) }}" width="{{ $w }}" height="{{ $h }}" @if($loop->first) fetchpriority="high" @else loading="lazy" decoding="async" @endif>
        </button>
        @endforeach
        @if($images->count() > 1)
        <button type="button" class="btn btn-light gallery-all" data-gallery-open="0">@include('site.partials.icon', ['name' => 'grid']) {{ __('messages.ShowAllPhotos') }} ({{ $images->count() }})</button>
        @endif
    </div>
    <script type="application/json" id="gallery-data">{!! $galleryJson !!}</script>
    @endif

    <div class="apt-layout">
        <div class="apt-main">

            <ul class="fact-list">
                <li>@include('site.partials.icon', ['name' => 'users'])<span><strong>{{ $guestsCount }}</strong> {{ __('messages.Guests') }}</span></li>
                <li>@include('site.partials.icon', ['name' => 'size'])<span><strong>{{ $size }} m²</strong> {{ __('site.studio') }}</span></li>
                <li>@include('site.partials.icon', ['name' => 'bed'])<span>{!! trans_choice('site.bed_count', $bedsCount, ['count' => '<strong>' . $bedsCount . '</strong>']) !!}</span></li>
                <li>@include('site.partials.icon', ['name' => 'bath'])<span><strong>{{ max(1, $bathroomsCount) }}</strong> {{ __('site.private_bathroom') }}</span></li>
            </ul>

            <section class="apt-section">
                <h2>{{ __('site.about_apartment') }}</h2>
                <div class="prose">{!! $descriptionHtml !!}</div>
                <ul class="key-features">
                    <li>@include('site.partials.icon', ['name' => 'parking']) {{ __('site.highlight_parking_title') }}</li>
                    <li>@include('site.partials.icon', ['name' => 'snowflake']) {{ __('site.air_conditioning') }}</li>
                    <li>@include('site.partials.icon', ['name' => 'wifi']) {{ __('site.free_wifi') }}</li>
                    <li>@include('site.partials.icon', ['name' => 'kitchen']) {{ __('site.kitchenette') }}</li>
                    <li>@include('site.partials.icon', ['name' => 'sun']) {{ __('site.balcony') }}</li>
                    <li>@include('site.partials.icon', ['name' => 'key']) {{ __('site.highlight_checkin_title') }}</li>
                </ul>
            </section>

            @if($accessories->isNotEmpty())
            <section class="apt-section">
                <h2>{{ __('messages.Amenities') }}</h2>
                <ul class="amenity-list" data-collapsible>
                    @foreach($accessories as $item)
                    <li @if($loop->index >= 12) class="is-extra" @endif>
                        @if(!empty($item->accessories->iconPath))
                        <svg class="icon" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path d="{{ $item->accessories->iconPath }}" fill="currentColor" fill-rule="evenodd"/></svg>
                        @else
                        @include('site.partials.icon', ['name' => 'check'])
                        @endif
                        <span>
                            {{ $item->accessories->displayName() }}
                            @if($item->accessories->displayDescription())
                            <small>{{ $item->accessories->displayDescription() }}</small>
                            @endif
                        </span>
                    </li>
                    @endforeach
                </ul>
                @if($accessories->count() > 12)
                <button type="button" class="btn btn-outline" data-collapsible-toggle data-label-less="{{ __('site.show_less') }}">{{ __('site.show_all_amenities', ['count' => $accessories->count()]) }}</button>
                @endif
            </section>
            @endif

            @if($houseRules)
            <section class="apt-section">
                <h2>{{ __('messages.HouseRules') }}</h2>
                <ul class="rule-list">
                    @foreach($houseRules as [$icon, $text])
                    <li>@include('site.partials.icon', ['name' => $icon]) {{ $text }}</li>
                    @endforeach
                </ul>
            </section>
            @endif
        </div>

        {{-- BOOKING --}}
        <aside class="apt-aside">
            <div class="booking-card" id="booking">
                <div class="booking-card-head">
                    @if($startingPrice)
                    <p class="booking-from"><span>{{ __('messages.From') }}</span> <strong>€{{ number_format($startingPrice, 0) }}</strong> / {{ __('messages.Night') }}</p>
                    @endif
                    <h2>{{ __('site.booking_title') }}</h2>
                </div>

                @if(session('booking-request-status'))
                <div class="alert alert-success" role="status">
                    @include('site.partials.icon', ['name' => 'check'])
                    <div>
                        <strong>{{ __('messages.YourRequestIsSuccessfulySent') }}</strong>
                        <p>{{ __('messages.WeWillGetBackToYou') }}</p>
                    </div>
                </div>
                @else
                <form action="{{ route('contact.booking.request', ['apartment_id' => $apartment->id]) }}" method="post" class="booking-form" data-date-range data-price-form>
                    @csrf
                    <div class="field-row">
                        <div class="field">
                            <label for="checkIn">{{ __('messages.CheckIn') }}</label>
                            <input id="checkIn" name="checkIn" type="text" inputmode="none" autocomplete="off" placeholder="dd.mm.yyyy" value="{{ old('checkIn', $checkIn) }}" required data-check-in @error('checkIn') aria-invalid="true" @enderror>
                        </div>
                        <div class="field">
                            <label for="checkOut">{{ __('messages.CheckOut') }}</label>
                            <input id="checkOut" name="checkOut" type="text" inputmode="none" autocomplete="off" placeholder="dd.mm.yyyy" value="{{ old('checkOut', $checkOut) }}" required data-check-out @error('checkOut') aria-invalid="true" @enderror>
                        </div>
                    </div>
                    <div class="field-row field-row-guests">
                        <div class="field">
                            <label for="firstAndLastName">{{ __('messages.FirstAndLastName') }}</label>
                            <input id="firstAndLastName" name="firstName" type="text" autocomplete="name" maxlength="100" value="{{ old('firstName') }}" required @error('firstName') aria-invalid="true" @enderror>
                        </div>
                        <div class="field">
                            <label for="guests">{{ __('messages.Guests') }}</label>
                            <input id="guests" name="guests" type="number" min="1" max="{{ max(1, $guestsCount) }}" value="{{ old('guests', min($sessionGuests ?? 2, max(1, $guestsCount))) }}" required data-guests @error('guests') aria-invalid="true" @enderror>
                        </div>
                    </div>
                    <div class="field">
                        <label for="mail">{{ __('messages.YourEMail') }}</label>
                        <input id="mail" name="email" type="email" inputmode="email" autocomplete="email" maxlength="150" value="{{ old('email') }}" required @error('email') aria-invalid="true" @enderror>
                    </div>
                    <details class="field-note" @if(old('note')) open @endif>
                        <summary>+ {{ __('messages.AddNote') }}</summary>
                        <label class="visually-hidden" for="note">{{ __('messages.Note') }}</label>
                        <textarea id="note" name="note" rows="3" maxlength="2000">{{ old('note') }}</textarea>
                    </details>

                    <dl class="price-lines">
                        <div><dt>{{ __('messages.FinalCleaning') }}</dt><dd>{{ __('messages.IncludedInPrice') }}</dd></div>
                        <div><dt>{{ __('messages.TouristTax') }}</dt><dd>{{ __('messages.IncludedInPrice') }}</dd></div>
                        <div class="price-total"><dt data-price-text>{{ $priceText }}</dt><dd data-price-amount>{{ $priceAmount }}</dd></div>
                    </dl>

                    <input type="hidden" value="{{ $apartment->id }}" data-apartment-id>
                    @include('site.partials.spam-guard', ['prefix' => 'booking'])

                    <button type="submit" class="btn btn-primary btn-block">{{ __('messages.SendRequest') }}</button>
                    <p class="booking-note">@include('site.partials.icon', ['name' => 'shield']) {{ __('site.booking_note') }}</p>
                </form>
                @endif

                <div class="booking-contact">
                    <span>{{ __('site.prefer_to_talk') }}</span>
                    <a href="https://wa.me/{{ ltrim($site['whatsapp'], '+') }}" target="_blank" rel="noopener">@include('site.partials.icon', ['name' => 'whatsapp']) WhatsApp</a>
                    <a href="tel:{{ $site['phone'] }}">@include('site.partials.icon', ['name' => 'phone']) {{ Seo::formatPhone($site['phone']) }}</a>
                </div>
            </div>
        </aside>
    </div>

    @if($reviews->isNotEmpty())
    <section class="apt-section apt-section-full" id="reviews">
        <h2>{{ __('messages.GuestReviews') }}</h2>
        @include('site.partials.reviews')
    </section>
    @endif

    <section class="apt-section apt-section-full" id="location">
        <h2>{{ __('site.location_title') }}</h2>
        <p class="section-lead">{{ __('site.location_p1') }}</p>
        <div class="map-frame map-frame-wide">
            <iframe src="{{ $site['google_maps_embed'] }}" title="{{ __('site.map_title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
        <div class="btn-row">
            <a class="btn btn-primary" href="{{ $site['google_maps_url'] }}" target="_blank" rel="noopener">@include('site.partials.icon', ['name' => 'map-pin']) {{ __('site.get_directions') }}</a>
            <a class="btn btn-ghost" href="{{ route('selfcheckin.locale', ['locale' => $lang]) }}">{{ __('site.arrival_guide') }} @include('site.partials.icon', ['name' => 'arrow-right'])</a>
        </div>
    </section>

    @if($otherApartments->isNotEmpty())
    <section class="apt-section apt-section-full">
        <h2>{{ __('site.other_apartments') }}</h2>
        <div class="apt-grid apt-grid-3">
            @foreach($otherApartments as $other)
                @include('site.partials.apartment-card', ['apartment' => $other])
            @endforeach
        </div>
    </section>
    @endif
</div>

{{-- Mobile booking bar --}}
<div class="mobile-book-bar" data-mobile-book-bar>
    <div>
        @if($startingPrice)
        <strong>€{{ number_format($startingPrice, 0) }}</strong> <span>/ {{ __('messages.Night') }}</span>
        @else
        <strong>{{ $name }}</strong>
        @endif
    </div>
    <a class="btn btn-primary" href="#booking">{{ __('site.request_booking') }}</a>
</div>

{{-- Lightbox --}}
<div class="lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="{{ __('messages.ShowAllPhotos') }}">
    <div class="lightbox-top">
        <span class="lightbox-count" data-lightbox-count></span>
        <button type="button" class="icon-btn icon-btn-dark" data-lightbox-close aria-label="{{ __('site.close') }}">@include('site.partials.icon', ['name' => 'x'])</button>
    </div>
    <figure class="lightbox-stage">
        <img src="" alt="" data-lightbox-img>
    </figure>
    <button type="button" class="icon-btn icon-btn-dark lightbox-prev" data-lightbox-prev aria-label="{{ __('site.previous') }}">@include('site.partials.icon', ['name' => 'chevron-left'])</button>
    <button type="button" class="icon-btn icon-btn-dark lightbox-next" data-lightbox-next aria-label="{{ __('site.next') }}">@include('site.partials.icon', ['name' => 'chevron-right'])</button>
</div>
@endsection
