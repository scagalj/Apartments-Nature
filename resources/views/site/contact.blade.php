@extends('site.layout')
@use('App\Support\Seo')

@php
    $site = config('site');
    $home = route('welcome.locale', ['locale' => $lang]);
    $schema = [
        Seo::lodgingBusiness($lang),
        [
            '@type' => 'ContactPage',
            'url' => route('contactus', ['locale' => $lang]),
            'name' => __('site.contact_title'),
            'about' => ['@id' => Seo::lodgingId()],
        ],
        Seo::breadcrumbs([
            [__('messages.Home'), $home],
            [__('messages.Contact'), route('contactus', ['locale' => $lang])],
        ]),
    ];
    $channels = [
        ['whatsapp', 'WhatsApp', Seo::formatPhone($site['whatsapp']), 'https://wa.me/' . ltrim($site['whatsapp'], '+'), true],
        ['phone', __('site.call_us'), Seo::formatPhone($site['phone']), 'tel:' . $site['phone'], false],
        ['mail', 'E-mail', $site['email'], 'mailto:' . $site['email'], false],
        ['map-pin', __('messages.Adress'), Seo::fullAddress(), $site['google_maps_url'], true],
    ];
@endphp

@section('title', __('site.meta_contact_title'))
@section('description', __('site.meta_contact_description'))
@section('body_class', 'page-contact')

@section('content')
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ $home }}">{{ __('messages.Home') }}</a></li>
                <li aria-current="page">{{ __('messages.Contact') }}</li>
            </ol>
        </nav>
        <h1>{{ __('site.contact_title') }}</h1>
        <p class="section-lead">{{ __('site.contact_lead') }}</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container contact-grid">
        <div class="contact-channels">
            @foreach($channels as [$icon, $label, $value, $href, $external])
            <a class="channel-card" href="{{ $href }}" @if($external) target="_blank" rel="noopener" @endif>
                <span class="channel-icon">@include('site.partials.icon', ['name' => $icon])</span>
                <span>
                    <span class="channel-label">{{ $label }}</span>
                    <span class="channel-value">{{ $value }}</span>
                </span>
            </a>
            @endforeach
            <div class="map-frame">
                <iframe src="{{ $site['google_maps_embed'] }}" title="{{ __('site.map_title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        </div>

        <div class="form-card" id="contact-form">
            <h2>{{ __('messages.HaveQuestion?') }}</h2>
            <p>{{ __('messages.HaveQuestionContactUs') }}</p>
            @if(session('booking-request-status'))
            <div class="alert alert-success" role="status">
                @include('site.partials.icon', ['name' => 'check'])
                <div>
                    <strong>{{ __('messages.YourRequestIsSuccessfulySent') }}</strong>
                    <p>{{ __('messages.WeWillGetBackToYou') }}</p>
                </div>
            </div>
            @else
            <form action="{{ route('contact.information.request') }}" method="post">
                @csrf
                <div class="field">
                    <label for="firstName">{{ __('messages.FirstName') }}</label>
                    <input id="firstName" name="firstName" type="text" autocomplete="given-name" maxlength="100" value="{{ old('firstName') }}" required @error('firstName') aria-invalid="true" @enderror>
                </div>
                <div class="field">
                    <label for="email">{{ __('site.email_address') }}</label>
                    <input id="email" name="email" type="email" inputmode="email" autocomplete="email" maxlength="150" value="{{ old('email') }}" required @error('email') aria-invalid="true" @enderror>
                </div>
                <div class="field">
                    <label for="note">{{ __('messages.WriteYourMessage') }}</label>
                    <textarea id="note" name="note" rows="6" maxlength="3000" required @error('note') aria-invalid="true" @enderror>{{ old('note') }}</textarea>
                </div>
                @include('site.partials.spam-guard', ['prefix' => 'contact'])
                <button type="submit" class="btn btn-primary btn-block">{{ __('messages.Send') }}</button>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection
