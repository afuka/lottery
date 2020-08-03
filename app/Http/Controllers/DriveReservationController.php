<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Service\ErrEnum;
use Illuminate\Support\Facades\Redis;
use App\Models\DriveReservation;

/**
 * 预约试驾
 */
class DriveReservationController extends Controller
{
    // 留资
    public function create(Request $request)
    {
        if(empty($request->get('name', ''))) return $this->result(ErrEnum::PARAM_ERR, '请填写姓名', []);
        if(empty($request->get('mobile', ''))) return $this->result(ErrEnum::PARAM_ERR, '请填写手机号', []);
        if(!preg_match("/^1[3456789]{1}\d{9}$/", $request->get('mobile'))) return $this->result(ErrEnum::PARAM_ERR, '手机号不符合规范', []);
        if(empty($request->get('car', ''))) return $this->result(ErrEnum::PARAM_ERR, '请选择车型', []);
        if(empty($request->get('province', ''))) return $this->result(ErrEnum::PARAM_ERR, '请选择省份信息', []);
        if(empty($request->get('city', ''))) return $this->result(ErrEnum::PARAM_ERR, '请选择省市信息', []);
        if(empty($request->get('dealer', ''))) return $this->result(ErrEnum::PARAM_ERR, '请选择经销商', []);
        if(empty($request->get('code', ''))) return $this->result(ErrEnum::PARAM_ERR, '经销商代码异常', []);

        if(Arr::get($request->activity->config, 'mobile_verify', '0') == '1') {
            //TODO: 验证短信验证码
        }
        $existsKey = 'DRIVE_RESERVATION_EXISTS_' 
            . $request->activity->id . '_' 
            . $request->get('source', 'default') . '_' 
            . $request->get('mobile');
        if(Redis::exists($existsKey)) return $this->result(ErrEnum::PARAM_ERR, '您已经提交过,请勿重复提交', []);
        Redis::set($existsKey, '1');

        $data = [
            'activity_id' => $request->activity->id,
            'source' => $request->get('source', 'default'),
            'name' => $request->get('name'),
            'mobile' => $request->get('mobile'),
            'gender' => $request->get('gender', '0'),
            'car' => $request->get('car'),
            'province' => $request->get('province'),
            'city' => $request->get('city'),
            'dealer' => $request->get('dealer'),
            'dealer_code' => $request->get('code'),
            'media' => $request->get('media', ''),
            'crm_sync' => $request->get('crm_sync', '0'),
            'ip' => $request->ip(),
        ];
        if(!empty($request->get('ordertime'))) $data['ordertime'] = $request->get('ordertime');
        if(!empty($request->get('buytime'))) $data['buytime'] = $request->get('buytime');

        try {
            $record = DriveReservation::create($data);
            $id = $record->id;
        } catch (\Exception $e) {
            Redis::del($existsKey);
            return $this->result(ErrEnum::DB_ERR, $e->getMessage(), []);
        }

        // 记录这条留资缓存
        $recordCacheKey = 'DRIVE_RESERVATION_INFO_' . $id;
        Redis::setex($recordCacheKey, 3600, json_encode_zw([
            'activity_id' => $request->activity->id,
            'source' => $request->get('source', 'default'),
            'name' => $request->get('name'),
            'mobile' => $request->get('mobile'),
            'car' => $request->get('car'),
        ]));

        return $this->result(0, 'success', [
            'source_id' => base64_encode($id), // 记录id
            'souce_type' => 'drive_reservation', // 记录来源
            'can_lottery' => 1, // 是否可去抽奖
        ]);
    }
}
