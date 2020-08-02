<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeLog extends Model
{
    protected $casts = [
        'ext_info' => 'json',
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

    /**
     * 归属奖品组
     *
     * @return void
     */
    public function prizegroup()
    {
        return $this->belongsTo('App\Models\PrizeGroup', 'group_id');
    }

    /**
     * 归属奖品
     *
     * @return void
     */
    public function prize()
    {
        return $this->belongsTo('App\Models\Prize', 'prize_id');
    }
}
