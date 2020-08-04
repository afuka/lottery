<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Service\ErrEnum;
use App\Service\Sms;
use Illuminate\Support\Facades\Redis;
use App\Models\PrizeLog;
use App\Models\PrizeGroup;
use App\Models\Prize;
use App\Service\Lottery;

/**
 * 抽奖
 */
class LotteryController extends Controller
{
    /**
     * 奖品列表
     */
    public function prizes(Request $request)
    {
        $prizeGroupId = base64_decode($request->get('prize_group_id', 0));
        if(empty($prizeGroupId)) return $this->result(ErrEnum::PARAM_ERR, '请指定奖品组', []);
        try {
            $groupSeri = unserialize(Redis::get('PRIZE_GROUP_' . $prizeGroupId));
            if(empty($groupSeri)) return $this->result(ErrEnum::NOT_EXISTS_ERR, '该奖品组不存在或已停用', []);
        } catch (\Exception $e) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, $e->getMessage(), []);
        }

        try {
            $ser = new Lottery($groupSeri);
            $ser->setRequest($request);
        } catch (\Exception $e) {
            return $this->result(ErrEnum::CONSTRUCT_ERR, $e->getMessage(), []);
        }

        $prizes = $ser->getPrizesByGroup();
        if(!is_array($prizes)) $prizes = $prizes->toArray();
        $result = array_map(function($item) {
            $image = $item['image'];
            if(!empty($image)) $image = config('filesystems.disks.admin.url') . '/' . $image;
            return [
                'id' => $item['id'],
                'prize_id' => base64_encode($item['id']),
                'name' => $item['name'],
                'bz' => $item['bz'],
                'type' => $item['type'],
                'image' => $image,
                'total' => $item['total'],
                'sort' => $item['sort'],
                'leave_info' => $item['leave_info'],
            ];
        }, $prizes);
        if(empty($result)) $result = [];

        return $this->result(0, 'success', $result);

    }

    /**
     * 进行抽奖
     */
    public function draw(Request $request)
    {
        $prizeGroupId = base64_decode($request->get('prize_group_id', 0));
        if(empty($prizeGroupId)) return $this->result(ErrEnum::PARAM_ERR, '请指定奖品组', []);
        // 是否有抽奖资格来源类型的限制
        $sourceType = $request->get('source_type', '');
        $sourceId = base64_decode($request->get('source_id', ''));
        if(empty($sourceType)) return $this->result(ErrEnum::PARAM_ERR, '请指定资格来源类型', []);
        if(empty($sourceId)) return $this->result(ErrEnum::PARAM_ERR, '请指定资格来源', []);

        try {
            $groupSeri = unserialize(Redis::get('PRIZE_GROUP_' . $prizeGroupId));
            if(empty($groupSeri)) return $this->result(ErrEnum::NOT_EXISTS_ERR, '该奖品组不存在或已停用', []);
        } catch (\Exception $e) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, $e->getMessage(), []);
        }
        
        try {
            $ser = new Lottery($groupSeri);
            $ser->setRequest($request);
        } catch (\Exception $e) {
            return $this->result(ErrEnum::CONSTRUCT_ERR, $e->getMessage(), []);
        }

        $ownCheckValue = ''; // 验证这个记录是否是这个人的
        if($sourceType == 'drive_reservation') {
            $ser->setRecordLimitOwnKey('mobile');
            if(empty($request->get('record_check', ''))) return $this->result(ErrEnum::PARAM_ERR, '不存在的数据校验值', []);
            $ownCheckValue = $request->get('record_check');
        }

        // 验证记录来源是否存在，以及这个是否在抽奖次数限制中
        $prize = $ser->draw($sourceType, $sourceId, $ownCheckValue);
        if($prize === false) {
            return $this->result(intval($ser->getErrCode()), $ser->getErr(), []);
        }

        return $this->result(0, 'success', [
            'is_prize' => empty($prize) ? '0' : '1', // 是否中奖, 0 否， 1中奖
            'prize' => empty($prize) ? [] : [
                'id' => base64_encode($prize->log_id),
                'prize_id' => base64_encode($prize->id),
                'name' => $prize->name,
                'bz' => $prize->bz,
                'type' => $prize->type,
                'image' => empty($prize->image) ? '' : config('filesystems.disks.admin.url') . '/' . $prize->image,
                'total' => $prize->total,
                'sort' => $prize->sort,
                'code' => $prize->ticket,
                'leave_info' => $prize->leave_info,
            ], // 奖品信息
        ]);
    }

    /**
     * 中奖后留资
     */
    public function leaveInfo(Request $request)
    {
        $logId = base64_decode($request->get('id', 0));
        $prizeId = base64_decode($request->get('prize_id', 0));
        if(empty($logId)) return $this->result(ErrEnum::PARAM_ERR, '请指定获奖记录', []);
        if(empty($prizeId)) return $this->result(ErrEnum::PARAM_ERR, '请指定获奖记录2', []);

        $name = $request->get('name');
        $mobile = $request->get('mobile');
        $addr = $request->get('addr', '');
        if(empty($name)) return $this->result(ErrEnum::PARAM_ERR, '请填写收件人姓名', []);
        if(empty($mobile)) return $this->result(ErrEnum::PARAM_ERR, '请填写收货手机号', []);
        // if(empty($addr)) return $this->result(ErrEnum::PARAM_ERR, '请填写收货地址', []);

        $key = 'LOTTERY_USER_GET_PRIZE_LOG_' . $logId;
        $record = json_decode(strval(Redis::get($key)), true);
        if(empty($record)) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, '不存在或已失效的来源记录数据', []);
        }
        if($prizeId != Arr::get($record, 'prize_id', 0)) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, '校验数据不符合', []);
        }
        if($mobile != Arr::get($record, 'mobile', 0)) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, '收件手机号与来源记录不符', []);
        }

        $existsKey = 'LOTTERY_LEAVE_INFO_FLAG_' . $logId;
        if(Redis::exists($existsKey)) return $this->result(ErrEnum::RUNTIME_ERR, '您已经留资过了,请勿重复提交', []);

        // 保存数据
        try {
            $record = PrizeLog::where('id', '=', $logId)->firstOrFail();
        } catch (\Exception $e) {
            return $this->result(ErrEnum::NOT_EXISTS_ERR, '不存在或已失效的来源记录数据2', []);
        }

        $record->ext_info = [
            'name' => $name,
            'mobile' => $mobile,
            'addr' => $addr,
        ];
        $record->save();

        Redis::set($existsKey, '1');

        // 发送短信
        if(!empty($record->code)) {
            $sms = new Sms();
            $res = $sms->send($mobile, $record->code);
        }

        return $this->result(0, 'success', []);
    }
}
