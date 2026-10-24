@php
    $checkIn = Session::get('checkIn');
    $checkOut = Session::get('checkOut');
    $guests = Session::get('guests');
    $datesSelected = !empty($checkIn) && !empty($checkOut);
    $price = $datesSelected ? $apartment->calculatePrice($checkIn, $checkOut, $guests) : $apartment->getStartingPrice();
    $mainImage = $apartment->getMainImage();
    $url = route('apartment.locale', ['locale' => app()->getLocale(), 'apartment' => $apartment->id]);
    $headingTag = $headingTag ?? 'h3';
@endphp
<article class="apt-card">
    <a class="apt-card-media" href="{{ $url }}" tabindex="-1" aria-hidden="true">
        @if($mainImage)
        @php [$w, $h] = $mainImage->dimensions(); @endphp
        <img src="{{ $apartment->imageUrl($mainImage) }}" alt="{{ $apartment->imageAlt($mainImage) }}" width="{{ $w }}" height="{{ $h }}" loading="lazy" decoding="async">
        @endif
        <span class="apt-card-badge">@include('site.partials.icon', ['name' => 'size']) {{ __('site.size_m2', ['size' => $apartment->sizeValue()]) }}</span>
    </a>
    <div class="apt-card-body">
        <{{ $headingTag }} class="apt-card-title"><a href="{{ $url }}">{{ $apartment->displayName() }}</a></{{ $headingTag }}>
        <ul class="apt-card-facts">
            <li>@include('site.partials.icon', ['name' => 'users']) {{ __('site.up_to_guests', ['count' => $apartment->getNumberOfGuests()]) }}</li>
            <li>@include('site.partials.icon', ['name' => 'sun']) {{ __('site.balcony') }}</li>
            <li>@include('site.partials.icon', ['name' => 'snowflake']) {{ __('site.air_conditioning') }}</li>
        </ul>
        <div class="apt-card-footer">
            <div class="apt-card-price">
                @if($price)
                    @if($datesSelected)
                    <span class="price-label">{{ __('messages.TotalPrice') }}</span>
                    <span class="price-value">€{{ number_format($price, 0) }}</span>
                    @else
                    <span class="price-label">{{ __('messages.From') }}</span>
                    <span class="price-value">€{{ number_format($price, 0) }}<small> / {{ __('messages.Night') }}</small></span>
                    @endif
                @else
                    <span class="price-label">{{ __('messages.Price') }}</span>
                    <span class="price-value price-request">{{ __('messages.OnRequest') }}</span>
                @endif
            </div>
            <a class="btn btn-outline" href="{{ $url }}">{{ __('site.view_apartment') }}</a>
        </div>
    </div>
</article>
