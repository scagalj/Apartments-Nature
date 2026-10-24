@extends('site.layout')

@php
    $steps = [
        [
            'title' => 'Directions when coming from Split, Makarska or Blato na Cetini',
            'image' => 'directions.jpg',
            'text' => [
                'If you are coming from the direction of Split, turn left onto "Mosorska cesta" and after 500 meters, turn left onto "Lisičina" street.',
                'If you are coming from the direction of Makarska, turn right onto "Mosorska cesta" and after 400 meters, turn left onto "Lisičina" street.',
                'If you are coming from the direction of the Blato na Cetini road, follow the road and after passing Hotel Villa Dvor, continue 200 meters and then turn right onto "Lisičina" street.',
            ],
        ],
        [
            'title' => 'Turn to our apartments and parking from the main street',
            'image' => 'turning.jpg',
            'text' => [
                'If you are coming from the direction of Split or Makarska, turn left onto "Lisičina" street. At the top of the hill on the left side, you will find the parking lot for Apartments Nature.',
                'If you are coming from the direction of Blato na Cetini, turn right onto "Lisičina" street. At the top of the hill on the left side, you will find the parking lot for Apartments Nature.',
            ],
        ],
        [
            'title' => 'Apartments Nature parking',
            'image' => 'parking.jpg',
            'text' => [
                'On the left side, you will find the parking lot for Apartments Nature, and you can freely leave your vehicle there. Parking is included in the price and does not incur any additional charges. On the right side, there is a path leading to your apartment.',
            ],
        ],
        [
            'title' => 'The Apartments Nature house',
            'image' => 'apartment-house.jpg',
            'text' => [
                'Follow the path for about 50 meters, and you will reach the entrance of Apartments Nature. The entrance is in the middle on the right and does not have any doors.',
            ],
        ],
        [
            'title' => 'Entrance to Apartments Nature',
            'image' => 'enterance.jpg',
            'text' => [
                'The entrance to Apartments Nature is marked with a green icon. Enter inside and continue upstairs to the floor of the apartment you have reserved.',
            ],
        ],
        [
            'title' => 'First floor apartments',
            'image' => 'first-floor.jpg',
            'list' => ['Studio apartment 1 (A1)', 'Studio apartment 5 (A5)'],
        ],
        [
            'title' => 'Second floor apartments',
            'image' => 'second-floor.jpg',
            'list' => ['Studio apartment 2 (A2)', 'Studio apartment 3 (A3)', 'Studio apartment 4 (A4)', 'Studio apartment 6 (A6)', 'Studio apartment 7 (A7)'],
        ],
    ];
@endphp

@section('title', 'Self check-in & directions | ' . config('site.name') . ' Omiš')
@section('description', 'How to find Apartments Nature in Omiš: driving directions, free private parking and self check-in instructions for your arrival.')
@section('noindex', '1')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Self check-in at Apartments Nature</h1>
        <p class="section-lead">Check-in between {{ config('site.check_in.from') }} and {{ config('site.check_in.until') }}. Your apartment will be ready on arrival and the key is on the inside of the apartment's entrance door.</p>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <ol class="steps">
            @foreach($steps as $step)
            <li class="step">
                <div class="step-copy">
                    <span class="step-number">{{ $loop->iteration }}</span>
                    <h2>{{ $step['title'] }}</h2>
                    @foreach($step['text'] ?? [] as $paragraph)
                    <p>{{ $paragraph }}</p>
                    @endforeach
                    @isset($step['list'])
                    <p>On this floor you will find:</p>
                    <ul class="check-list">
                        @foreach($step['list'] as $item)
                        <li>@include('site.partials.icon', ['name' => 'check']) {{ $item }}</li>
                        @endforeach
                    </ul>
                    @endisset
                </div>
                <figure class="step-media">
                    <img src="{{ asset('images/selfcheckin/' . $step['image']) }}" alt="{{ $step['title'] }}" loading="lazy" decoding="async">
                </figure>
            </li>
            @endforeach
        </ol>

        <div class="notice">
            @include('site.partials.icon', ['name' => 'key'])
            <p>When you leave, please leave the key on the inside of your apartment door, and remember to turn off the lights and the air conditioning. Check-out is until {{ config('site.check_out') }}.</p>
        </div>
    </div>
</section>
@endsection
