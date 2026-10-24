<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Apartment;

/**
 * Description of ApartmentReviews
 *
 * @author Stjepan
 */
class ApartmentReviews extends Model {

    protected $table = 'apartmentreviews';
    protected $fillable = [
        'firstName',
        'lastName',
        'title',
        'positiveComment',
        'negativeComment',
        'ratingNumber',
        'countryCode',
        'bookingStartDate',
        'bookingEndDate',
        'numberOfGuests',
        'reviewDate',
    ];

    public function apartment() {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Average score (out of 10) and number of rated reviews.
     */
    public static function summary() {
        static $summary = null;
        if ($summary === null) {
            $rated = self::whereNotNull('ratingNumber')->where('ratingNumber', '>', 0);
            $count = (clone $rated)->count();
            $summary = [
                'count' => $count,
                'average' => $count > 0 ? round((float) $rated->avg('ratingNumber'), 1) : null,
            ];
        }
        return $summary;
    }

    public static function latestRated($limit) {
        return self::whereNotNull('ratingNumber')->orderByDesc('reviewDate')->limit($limit)->get();
    }

    public static function newestFirst() {
        return self::orderByDesc('reviewDate')->get();
    }

}
