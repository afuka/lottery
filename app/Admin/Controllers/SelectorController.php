<?php

namespace App\Admin\Controllers;

use App\Models as Models;
use Illuminate\Http\Request;

class SelectorController extends AuthController
{
    /**
     * 获取活动列表
     */
    public function getActivityOption()
    {
        // 搜索出当前可用的
        $activities = Models\Activity::where('status', '1')->get();
 
        $result = [];
        foreach($activities as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return $result;
    }

    /**
     * 获取奖品组列表
     */
    public function getPrizeGroupOption()
    {
        // 搜索出当前可用的
        $groups = Models\PrizeGroup::where('status', '1')->get();
        
        $result = [];
        foreach($groups as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return $result;
    }
}
