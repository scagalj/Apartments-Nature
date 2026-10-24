<?php

/*
|--------------------------------------------------------------------------
| Public site / business details
|--------------------------------------------------------------------------
|
| Single source of truth for the business name, address and contact details
| (NAP). They are printed in the header, footer, contact page and in the
| structured data Google reads, so they must be identical everywhere and
| match the Google Business Profile / Booking.com / Airbnb listings exactly.
|
*/

return [

    'name' => 'Apartments Nature',

    'address' => [
        'street' => 'Lisičina 2',
        'postal_code' => '21310',
        'city' => 'Omiš',
        'region' => 'Split-Dalmatia County',
        'country' => 'Croatia',
        'country_code' => 'HR',
    ],

    // Coordinates of Lisičina street (OpenStreetMap). Adjust to the exact
    // pin of the Google Business Profile if it differs.
    'geo' => [
        'lat' => 43.44726,
        'lng' => 16.69069,
    ],

    'email' => 'apartmentsnature2@gmail.com',

    // Where booking and contact requests from the website are delivered.
    'booking_email' => env('BOOKING_REQUEST_EMAIL') ?: 'cagalj95@gmail.com',

    // E.164 format, no spaces.
    'phone' => '+385919285696',
    'whatsapp' => '+385915933575',

    'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Apartments+Nature+Lisi%C4%8Dina+2+Omi%C5%A1',
    'google_maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5793.658165061611!2d16.68513861313894!3d43.44328550964453!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x134a9842f9b61385%3A0x8987116b5779943c!2zTGlzacSNaW5hIDIsIDIxMzEwLCBPbWnFoQ!5e0!3m2!1shr!2shr!4v1684258887423!5m2!1shr!2shr',

    // Public profiles of the same property (Booking.com, Airbnb, Google
    // Business Profile, Facebook, Instagram ...). Every URL added here is
    // shown as a "Find us on" trust link and is added to the structured data
    // `sameAs`, which helps Google connect this website with those listings.
    'profiles' => [
        // 'Booking.com' => 'https://www.booking.com/hotel/hr/....html',
        // 'Airbnb' => 'https://www.airbnb.com/rooms/...',
        // 'Google' => 'https://g.page/...',
        // 'Instagram' => 'https://www.instagram.com/...',
        // 'Facebook' => 'https://www.facebook.com/...',
    ],

    'check_in' => ['from' => '13:00', 'until' => '22:00'],
    'check_out' => '11:00',

    // Size shown when an apartment has no size entered in the admin (m²).
    'default_apartment_size' => 25,

    // Order in which apartments are listed. Apartments not listed here are
    // appended at the end.
    'apartment_order' => [2, 1, 3, 4, 5, 6, 7],

    // Approximate distances from the house, shown in the location section.
    // Keys are translated in resources/lang/{locale}/site.php (distance_*).
    'distances' => [
        'cetina' => '150 m',
        'old_town' => '700 m',
        'beach' => '800 m',
        'split' => '25 km',
        'airport' => '50 km',
        'makarska' => '35 km',
    ],

    'google_analytics_id' => 'G-3DE6ENVC2J',

];
