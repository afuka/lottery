<?php

namespace App\Service;

use App\Repository\Dealers\Northeast;
use App\Traits\ErrConsoler;
use App\Models;
use Illuminate\Support\Facades\DB;


class SupplierCrm
{
    use ErrConsoler;

    protected $handler = null;

    public function __construct()
    {
        $this->handler = new Northeast();
    }

    /**
     * 同步并保存经销商
     */
    public function syncDealers()
    {
        try {
            $recodes = $this->handler->getDealers();
        } catch (\Exception $e) {
            $this->setErr(ErrEnum::RUNTIME_ERR, $e->getMessage());
            return false;
        }

        // // 创建记录
        foreach ($recodes as $recode) {
            $code = $recode['code'];
            $dealer = Models\Dealer::where('code', '=', $code)->first();
            if(empty($dealer)) { // 新增
                $dealer = Models\Dealer::create($recode);
            } else { // 更新
                foreach($recode as $key => $value) {
                    $dealer->$key = $value;
                }
                $dealer->save();
            }
        }

        // 生成经销商js文件
        $dealers = Models\Dealer::where('status', '=', '1')->orderBy(Db::raw('
            CONVERT(province USING gbk) COLLATE gbk_chinese_ci ASC, 
            CONVERT(city USING gbk) COLLATE gbk_chinese_ci ASC, 
            CONVERT(simplify USING gbk) COLLATE gbk_chinese_ci'
        ), 'ASC')->get();

        $list = [];
        foreach ($dealers as $item) {
            $list[] = [
                'pro' => $item->province,
                'city' => $item->city,
                'dealer' => $item->simplify,
                'dealerfull' => $item->name,
                'code' => $item->code,
                'tel' => $item->tel,
                'addr' => $item->addr,
            ];
        }
        $list = ['Information' => $list];
        $content = 'var JSonData=' . json_encode_zw($list);

        try {
            // 创建目录
            if(!is_dir('./storage/app/public/caches')) {
                mkdir('./storage/app/public/caches', 0777, true);
            }
            // 创建文件
            $file = fopen('./storage/app/public/caches/dealer.js', 'w');
            fwrite($file, $content);
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception('文件生成失败' . $e->getMessage(), 1);
        }
        
        return true;
    }

    /**
     * 推送销售线索记录
     */
    public function pushRecord(Models\DriveReservation $record)
    {
        try {
            $activity = Models\Activity::where('id', '=', $record->activity_id)->firstOrFail();
        } catch (\Exception $e) {
            $this->setErr(ErrEnum::RUNTIME_ERR, $e->getMessage());
            return false;
        }

        try {
            $this->handler->pushRecord($activity, $record);
            $record->sync_status = '1';
            $record->save();
        } catch (\Exception $e) {
            echo $e->getMessage(), "\n";
            $record->sync_status = '-1';
            $record->save();
        }

        return true;
    }
}