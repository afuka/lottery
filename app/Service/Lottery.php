<?php

namespace App\Service;

use App\Traits\ErrConsoler;
use App\Models;
use App\Service\ErrEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class Lottery 
{
    use ErrConsoler;

    protected $prizeGroup = null; // 奖品组
    protected $recordLimitCheckKey = 'mobile'; // 记录中用户校验中奖次数的 key 是什么
    protected $request = null;

    public function __construct(Models\PrizeGroup $group)
    {
        $this->prizeGroup = $group;
    }

    /**
     * 设置 记录中用户校验中奖次数的 key
     * 
     * @param string $key
     * 
     * @return void
     */
    public function setRecordLimitOwnKey(string $key)
    {
        $this->recordLimitCheckKey = $key;
    }

    /**
     * 将 request 对象传进来
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获取奖品组中的奖品
     * 
     * @param Models\PrizeGroup 
     * @return array
     */
    public function getPrizesByGroup()
    {
        $prizeListKey = 'PRIZE_GROUP_PRIZES_' . $this->prizeGroup->id;
        try {
            $prizes = unserialize(Redis::get($prizeListKey));
            if(empty($prizes)) return [];
        } catch (\Exception $e) {
            return [];
        }

        return $prizes;
    }

    /**
     * 开始抽奖
     * 
     * @param string $sourceType
     * @param int $sourceId
     * 
     * @return array|bool
     */
    public function draw($sourceType, $sourceId, $ownCheckValue = '')
    {
        // 校验来源数据，是否可以抽奖
        $sourceRecord = $this->GetSourceRecordAndcheckQualification($sourceType, $sourceId);
        if($sourceRecord === false) return false;

        $prizeList = $this->getPrizesByGroup();
        if(empty($prizeList)) {
            $this->setErr(ErrEnum::NOT_EXISTS_ERR, '当前奖品组未设置奖品');
            return false;
        }

        // 校验这个记录归属
        if(
            !empty($ownCheckValue) 
            && Arr::get($sourceRecord, $this->recordLimitCheckKey, '') != $ownCheckValue
        ) {
            $this->setErr(ErrEnum::AUTH_ERR, '数据归属校验失败');
            return false;
        }

        // 校验这个人是否已经中过奖, 其中 record 中 ownCheckValue 是必须识别单位
        if(in_array($this->prizeGroup->user_limit_mode, ['once_per_group', 'once_per_activity'])) {
            $recordLimitCheck = Arr::get($sourceRecord, $this->recordLimitCheckKey, '');
            if(empty($recordLimitCheck)) {
                $this->setErr(ErrEnum::NOT_EXISTS_ERR, '资格来源记录中缺少校验数据信息');
                return false;
            }
            if($this->prizeGroup->user_limit_mode == 'once_per_group') {
                $key = 'LOTTERY_PRIZE_GET_USER_LIMIT_BY_GROUP_' . $this->prizeGroup->id . '_' . $ownCheckValue;
            }
            if($this->prizeGroup->user_limit_mode == 'once_per_activity') {
                $key = 'LOTTERY_PRIZE_GET_USER_LIMIT_BY_ACTIVITY_' . $this->prizeGroup->activity_id . '_' . $ownCheckValue;
            }
            $num = Redis::incr($key);
            if($num > 1) {
                Redis::decr($key);
                return []; // 假装抽完了，没中奖
            }
        }

        // 来个锁
        $lockKey = 'LOTTERY_GROUP_LOCK_' . $this->prizeGroup->id;
        $lock = Redis::incr($lockKey);
        if($lock > 1) {
            Redis::decr($lockKey);
            return [];
        }

        $defaultPrize = null; // 默认奖品
        foreach($prizeList as $item) {
            if($item->is_default == '1') $defaultPrize = $item;
        }

        $prizeMatch = $this->drawSinglePrizeFromGroup($sourceType, $sourceId, $prizeList); // 是否中奖
        if(empty($prizeMatch)) $prizeMatch = $defaultPrize;
        if(empty($prizeMatch)) {
            Redis::decr($lockKey); // 解锁
            return [];
        }

        // 判断是否有券
        $ticket = '';
        if($prizeMatch->type == 'coupon') {
            $ticketPoolKey = 'LOTTERY_PRIZE_TICKETS_ALIVE_' . $prizeMatch->id;
            $ticket = Redis::spop($ticketPoolKey);
            if(empty($ticket)) {
                Redis::decr($lockKey); // 解锁
                return [];
            }
            $ticket = current($ticket);
        }

        // 设置中奖的缓存
        Redis::incr('LOTTERY_PRIZE_SEND_COUNT_' . $prizeMatch->id); // 奖品发出去了
        if(!empty($ownCheckValue)) $this->sendsLimitCacheKeyValueIncr($ownCheckValue, $prizeMatch); // 如果有限制的话
        Redis::decr($lockKey); // 解锁

        // 加入数据库
        try {
            $ip = '0.0.0.0';
            if(!empty($this->request)) $ip = $this->request->ip();

            $data = [
                'activity_id' => $this->prizeGroup->activity_id,
                'group_id' => $this->prizeGroup->id,
                'prize_id' => $prizeMatch->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'code' => $ticket,
                'ip' => $ip,
                'mobile' => $ownCheckValue,
            ];
            $log = Models\PrizeLog::create($data);
            
            $prizeMatch->log_id = $log->id;
            $prizeMatch->ticket = $ticket;

            $key = 'LOTTERY_USER_GET_PRIZE_LOG_' . $log->id;
            Redis::setex($key, 3600, json_encode_zw($data));
        } catch(\Exception $e) {
            return []; // 异常了，就假装没中奖吧
        }

        return $prizeMatch;
    }

    /**
     * 设置发放数量的缓存
     */
    protected function sendsLimitCacheKeyValueIncr($ownCheckValue, $prize)
    {
        $key = 'LOTTERY_PRIZE_GET_USER_LIMIT_BY_GROUP_' . $this->prizeGroup->id . '_' . $ownCheckValue;
        Redis::incr($key);
        $key = 'LOTTERY_PRIZE_GET_USER_LIMIT_BY_ACTIVITY_' . $this->prizeGroup->activity_id . '_' . $ownCheckValue;
        Redis::incr($key);
    }

    /**
     * 抽取一个奖品
     */
    protected function drawSinglePrizeFromGroup($sourceType, $sourceId, $prizeList)
    {
        $prizeMatch = null;
        $now = time();
        $probabilityDenominator = 10000; // 中奖概率分母为
        $nowProbability = 0; // 当前分子
        $rand = mt_rand(0, $probabilityDenominator); // 抽奖的随机概率
        foreach($prizeList as $item) {
            if($item->status != '1') continue;
            if(intval($item->probability) <= 0) continue;
            // 看看概率中了没
            $nowProbability += intval($item->probability);
            if($rand > $nowProbability) continue;
            // 查看奖品总数是否发完
            $prizeSendedTotalKey = 'LOTTERY_PRIZE_SEND_COUNT_' . $item->id;
            $prizeSendedNum = Redis::incr($prizeSendedTotalKey);
            if($prizeSendedNum > intval($item->total)) {
                Redis::decr($prizeSendedTotalKey);
                continue;
            }
            Redis::decr($prizeSendedTotalKey);

            // 验证今日奖品可发放数
            if(!empty($item->date_limit)) {
                $todayAllowSendedNum = 0;
                foreach ($item->date_limit as $_d) {
                    if(strtotime($_d['datetime']) < $now) $todayAllowSendedNum += intval($_d['num']);
                }
                if($prizeSendedNum > $todayAllowSendedNum) continue;
            }

            // 匹配到了准备发奖
            $prizeMatch = $item;
        }

        return $prizeMatch;
    }

    /**
     * 获取资格来源记录主要数据, 校验当前是否可抽奖
     * 
     * @param string $sourceType
     * @param int $sourceId
     * 
     * @return array|bool 
     */
    protected function GetSourceRecordAndcheckQualification($sourceType, $sourceId)
    {
        $groupLimitSourceType = Arr::get($this->prizeGroup->config, 'source_type', '');
        if($groupLimitSourceType != $sourceType) {
            $this->setErr(ErrEnum::AUTH_ERR, '资格来源不符');
            return false;
        }

        // 查找来源记录
        $key = '';
        if($sourceType == 'drive_reservation') { // 预约试驾
            $key = 'DRIVE_RESERVATION_INFO_' . $sourceId;
        }
        if(empty($key)) {
            $this->setErr(ErrEnum::UNSUPPORTED, '未找到支持的类型驱动');
            return false;
        }
        $record = json_decode(strval(Redis::get($key)), true);
        if(empty($record)) {
            $this->setErr(ErrEnum::NOT_EXISTS_ERR, '不存在或已失效的来源记录数据');
            return false;
        }

        $limitTimes = Arr::get($this->prizeGroup->config, 'limit_times', 0);

        // LOTTERY_TIMES_LIMIT_{奖品组Id}_{资格来源}_{记录Id}
        $key = 'LOTTERY_TIMES_LIMIT_' . $this->prizeGroup->id . '_' . $sourceType . '_' . $sourceId;
        $nowTime = Redis::incr($key); 
        if($nowTime > $limitTimes) {
            $this->setErr(ErrEnum::AUTH_ERR, '抽奖次数已用完');
            Redis::decr($key);
            return false;
        }

        return $record;
    }

}