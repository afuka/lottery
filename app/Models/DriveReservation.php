<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriveReservation extends Model
{
    /**
     * 可以被批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'source','name','mobile','activity_id','gender','province',
        'city','dealer','dealer_code','media','crm_sync','ip','ordertime','buytime','car'
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
