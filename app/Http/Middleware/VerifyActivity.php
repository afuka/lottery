<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use App\Models\Activity;

class VerifyActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 获取活动
        $activityId = $request->activityId;
        if(empty($activityId)) {
            return response('不存在的活动', 404);
        }
        try {
            $activitySeri = unserialize(Redis::get('ACTIVITY_' . $activityId));
            if(empty($activitySeri)) return response('不存在的活动信息', 404);
        } catch (\Exception $e) {
            return response('活动数据异常', 404);
        }

        // 验证活动
        $now = time();
        if($activitySeri->status != '1') return response('活动终止', 404);
        if(strtotime($activitySeri->started) > $now) return response('活动暂未开始', 404);
        if(strtotime($activitySeri->ended) < $now) return response('活动已结束', 404);

        $request->activity = $activitySeri;

        return $next($request);
    }
}
