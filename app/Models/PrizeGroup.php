<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeGroup extends Model
{
    protected $casts = [
        'config' => 'json',
    ];

    /**
     * 归属活动
     *
     * @return void
     */
    public function activity()
    {
        return $this->belongsTo('App\Models\Activity', 'activity_id');
    }
}
