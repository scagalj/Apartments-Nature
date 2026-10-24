<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessories extends Model {

    protected $table = 'accessories';
    protected $fillable = [
        'name',
        'description',
        'iconName',
        'accessoriesType',
        'request',
        'popular',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }

    /**
     * Translated name, or the stored name when there is no translation yet.
     */
    public function displayName() {
        return self::translate($this->name);
    }

    public function displayDescription() {
        return empty($this->description) ? '' : self::translate($this->description);
    }

    private static function translate($text) {
        return \Illuminate\Support\Facades\Lang::has('accessories.' . $text) ? __('accessories.' . $text) : $text;
    }

}
