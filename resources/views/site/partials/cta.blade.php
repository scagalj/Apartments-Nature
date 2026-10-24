@php $site = config('site'); @endphp
<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2>{{ __('site.cta_title') }}</h2>
            <p>{{ __('site.cta_text') }}</p>
        </div>
        <div class="cta-actions">
            <a class="btn btn-light" href="https://wa.me/{{ ltrim($site['whatsapp'], '+') }}" target="_blank" rel="noopener">@include('site.partials.icon', ['name' => 'whatsapp']) WhatsApp</a>
            <a class="btn btn-outline-light" href="tel:{{ $site['phone'] }}">@include('site.partials.icon', ['name' => 'phone']) {{ __('site.call_us') }}</a>
            <a class="btn btn-outline-light" href="{{ route('contactus', ['locale' => app()->getLocale()]) }}">@include('site.partials.icon', ['name' => 'mail']) {{ __('site.send_message') }}</a>
        </div>
    </div>
</section>
