<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use App\Http\Controllers\LangController;
use App\Models\ApartmentReviews;
use App\Models\ApartmentPrices;
use App\Models\House;
use App\Models\Images;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;

/**
 * Description of Apartment
 *
 * @author Stjepan
 */
class Apartment extends Model {
    /* Fields:
     * - id
     * - name
     * - list of rooms;  
     * 
     */

//    protected $transient = [
//        'descriptionit' => null,
//    ];

    protected $table = 'apartment';
    protected $fillable = [
        'name',
        'internalName',
        'apartmentType',
        'size',
        'note',
        'description'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function house() {
        return $this->belongsTo(House::class);
    }

    public function room() {
        return $this->hasMany(Room::class);
    }

    public function images() {
        return $this->hasMany(Images::class);
    }

    public function apartmentReviews() {
        return $this->hasMany(ApartmentReviews::class);
    }

    public function apartmentPrices() {
        return $this->hasMany(ApartmentPrices::class);
    }

    public function getdescriptionvalue($lang) {
        return $this->getDescriptionLang()->getValue($lang);
    }

    public function getDescriptionLang() {
        $description = LangController::convertJsonToLanguageField($this->description);
        return $description;
    }

    public function getImagesPath() {
        return '/images/apartments/' . $this->id . '/';
    }

    public function getMainImage() {
        return $this->images()->where('mainimage', true)->first();
    }

    public function getMainImages() {
        return $this->images()->where('mainimage', true);
    }

    public function getOtherImages() {
        return $this->images()->where('mainimage', false)->get();
    }

    public function getImagesByRange($indexFrom, $imageCount = 2) {
        return $this->images()->where('mainimage', false)->get()->slice($indexFrom, $imageCount);
    }

    public function getAllImagesToDisplay() {
        $c = collect();
        $mainImages = $this->getMainImage();
        $c->add($mainImages);
        $otherImages = $this->getOtherImages();
        return $c->concat($otherImages);
    }

    public function getNumberOfGuests() {
        $roomsWithBeds = $this->getAllRoomsWithBed();
        $guestCounter = 0;
        foreach ($roomsWithBeds as $roomsWithBed) {
            $guestCounter = $guestCounter + $roomsWithBed->getNumberOfGuests();
        }
        return $guestCounter;
    }

    public function getNumberOfBedrooms() {
        $numberOfBedrooms = 0;
        $rooms = $this->room()->get();
        foreach ($rooms as $room) {
            if ($room->isBedroom()) {
                $numberOfBedrooms = $numberOfBedrooms + 1;
            }
        }
        return $numberOfBedrooms;
    }

    public function getNumberOfBeds() {
        $numberOfBeds = 0;
        $rooms = $this->room()->get();
        foreach ($rooms as $room) {
            foreach ($room->beds as $bed) {
                $numberOfBeds = $numberOfBeds + $bed->numberOfBeds;
            }
        }

        return $numberOfBeds;
    }

    public function getNumberOfBathrooms() {
        $numberOfBathrooms = 0;
        $rooms = $this->room()->get();
        foreach ($rooms as $room) {
            $numberOfBathrooms = $numberOfBathrooms + sizeof($room->bathroom()->get());
        }
        return $numberOfBathrooms;
    }

    public function getAllRoomsWithBed() {
        $allrooms = $this->room()->get();
        $c = collect();
        foreach ($allrooms as $room) {
            if (($room->beds()->first())) {
                $c->add($room);
            }
        }
        return $c;
    }

    public function getAllAccessoriesWithPopularFirst() {
        if (empty($this->room())) {
            return null;
        }
        if (is_null($this->room())) {
            return null;
        }
        if (is_null($this->room()->first())) {
            return null;
        }
        if (empty($this->room()->first()->roomaccessories())) {
            return null;
        }
        $allAccessories = $this->room()->first()->roomaccessories()->get();
        $popularAccessories = collect();
        $otherAccessories = collect();
        foreach ($allAccessories as $roomaccessories) {
            if (filter_var($roomaccessories->accessories->popular, FILTER_VALIDATE_BOOLEAN)) {
                $popularAccessories->add($roomaccessories);
            } else {
                $otherAccessories->add($roomaccessories);
            }
        }
        return $popularAccessories->concat($otherAccessories);
    }

    public function getAllAccessories() {
        $allAccessories = $this->room()->first()->roomaccessories()->get();
        return $allAccessories;
    }

    public function calculatePrice($periodFrom, $periodTo, $guests) {
        $amount = 0;
        try {

            if (!isset($periodFrom)) {
                $periodFrom = date('d.m.Y');
            }

            if (!isset($periodTo)) {
                $periodTo = date('d.m.Y', strtotime($periodFrom . ' +1 day')); // Add one day
            }

            if (!isset($guests)) {
                $guests = $this->getNumberOfGuests();
            }
//            error_log('DATE; 1 ' . $guests . ' - ' . $this->getNumberOfGuests());

            $priceDivisor = 1;
            if ($guests < $this->getNumberOfGuests()) {
//                $priceDivisor = 0.90;
                $priceDivisor = 1;
            }
//            error_log('DATE; 2 ' . $priceDivisor);

            $dateFrom = Carbon::createFromFormat('d.m.Y', $periodFrom);
            $dateTo = Carbon::createFromFormat('d.m.Y', $periodTo);

            $days = (int) $dateFrom->diffInDays($dateTo, true);

//            error_log('DATE; 3 ' . $days);
            if ($days < 1 || $days > 365) {
                return 0;
            }

            while ($dateFrom < $dateTo) {

                $price = $this->calculatePriceForDate($dateFrom);
                //Ako neki period nema cijenu vrati 0 kao da taj period nije bookabilan.
                if ($price == null) {
                    return 0;
                }

                $amount += ($price->price * $priceDivisor);
                $dateFrom->addDay();
            }
        } catch (\Exception $e) {
            return 0;
        }
        return $amount;
    }

    private function calculatePriceForDate($date) {
        $apartmentPrices = $this->apartmentPrices()->get();
        foreach ($apartmentPrices as $price) {
            if ($date->between($price->fromDate, $price->toDate)) {
                return $price;
            }
        }
        return null;
    }

    public function getPriceAmountTextForApartment($isDatesDefined, $price) {
        $priceCurrency = ' € ';
        if ($price != 0) {
            return $isDatesDefined ? $priceCurrency . number_format($price, 2) : $priceCurrency . number_format($price, 2) . ' / ' . __('messages.Night');
        }
        return '';
    }

    public function getPriceValueTextForApartment($isDatesDefined, $price) {
        if ($price == 0) {
            return __('messages.OnRequest');
        } else {
            return $isDatesDefined ? __('messages.TotalPrice') : __('messages.From');
        }
    }

    /**
     * Translated public name, e.g. "Studio apartment 3".
     */
    public function displayName() {
        $key = 'messages.' . $this->name;
        return Lang::has($key) ? __($key) : $this->name;
    }

    /**
     * Size in m², falling back to the default size from config/site.php.
     */
    public function sizeValue() {
        $size = (int) preg_replace('/[^0-9]/', '', (string) $this->size);
        return $size > 0 ? $size : config('site.default_apartment_size');
    }

    /**
     * Lowest nightly price of the current and upcoming price periods.
     */
    public function getStartingPrice() {
        $price = $this->apartmentPrices()->where('toDate', '>=', Carbon::today())->min('price');
        return $price ? (float) $price : null;
    }

    public function plainDescription($lang) {
        $description = $this->getdescriptionvalue($lang) ?: $this->getdescriptionvalue(LangController::getDefaultLanguage());
        return trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $description))));
    }

    public function imageUrl($image) {
        return $this->getImagesPath() . rawurlencode($image->filename);
    }

    /**
     * Descriptive alt text: the image description from the admin, or the
     * apartment name with the location.
     */
    public function imageAlt($image, $index = 0) {
        if (!empty($image->description)) {
            return $image->description;
        }
        $alt = __('site.image_alt', ['apartment' => $this->displayName()]);
        return $index > 0 ? $alt . ' – ' . __('site.photo') . ' ' . ($index + 1) : $alt;
    }

    public function getAllImages() {
        return $this->getAllImagesToDisplay()->filter()->values();
    }

//    public function getOtherImages() {
//        return $this->images()->where('mainimage', false);
//        $otherImages = array();
//        foreach ($otherImages as $this->images => $image) {
//            if (!$image->mainimage) {
//                $otherImages[] = $image;
//            }
//        }
//        return $otherImages;
//    }
}
