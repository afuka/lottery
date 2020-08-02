<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $casts = [
        'date_limit' => 'json',
        'config' => 'json',
    ];

    /**
     * 归属奖品组
     *
     * @return void
     */
    public function prizegroup()
    {
        return $this->belongsTo('App\Models\PrizeGroup', 'group_id');
    }

    public function getDateLimitAttribute($value)
    {
        return array_values(json_decode($value, true) ?: []);
    }

    public function setDateLimitAttribute($value)
    {
        $this->attributes['date_limit'] = json_encode(array_values($value));
    }
}
