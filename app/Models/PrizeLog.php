<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeLog extends Model
{
    protected $casts = [
        'ext_info' => 'json',
    ];

    /**
     * 可以被批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'activity_id','group_id','prize_id','source_type','source_id','code','ip', 'mobile'
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
