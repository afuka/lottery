<?php

namespace App\Repository\Exports;

use Illuminate\Support\Arr;
use App\Models\PrizeLog as PrizeLogModel;

/**
 * 导出
 */
class PrizeLog
{
    public function run(array $params)
    {
        $activityId = Arr::get($params, 'activity_id', 0);
        $started = Arr::get($params, 'started');
        $ended = Arr::get($params, 'ended');

        if(empty($started) || empty($ended)) {
            throw new \Exception('未指定导出起止时间' . $e->getMessage(), 1);
        }

        try {
            // 创建目录
            if(!is_dir('./storage/app/public/exports/prizelog/' . date('Y/md'))) {
                mkdir('./storage/app/public/exports/prizelog/' . date('Y/md'), 0777, true);
            }
            // 创建文件
            $fname = '/exports/prizelog/' . date('Y/md/') . time() . mt_rand(10000, 99999) . '.csv';
            $file = fopen('./storage/app/public' . $fname, 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));
        } catch (\Exception $e) {
            throw new \Exception('文件生成失败' . $e->getMessage(), 1);
        }

        // 头
        $header = [
            'Id', '归属活动Id', '归属活动', '奖品组Id', '奖品组', '奖品Id', '奖品', 
            '抽奖资格来源', '来源Id', '券码', '资格识别码', 'IP', '创建时间', '更新时间', 
            '状态', '留资姓名', '留资手机号', '留资地址'
        ];
        fputcsv($file, $header);

        $statusDic = ['-1' => '已删除', '0' => '删除' ,'1' => '正常'];
        $sourceTypeDic = ['' => '无', 'drive_reservation' => '预约试驾'];

        $model = PrizeLogModel::with('activity')
            ->with('prizegroup')->with('prize')
            ->whereBetween('created_at', [$started, $ended]);
        if(!empty($activityId)) $model->where('activity_id', $activityId);

        $records = $model->chunk(200, function($records) use ($file, $statusDic, $sourceTypeDic) {
            foreach($records as $record) {
                $row = [
                    $record->id,
                    $record->activity_id,
                    $record->activity->name,
                    $record->group_id,
                    $record->prizegroup->name,
                    $record->prize_id,
                    $record->prize->name,
                    Arr::get($sourceTypeDic, $record->source_type, $record->source_type),
                    $record->source_id,
                    $record->code,
                    $record->mobile,
                    $record->ip,
                    $record->created_at,
                    $record->updated_at,
                    Arr::get($statusDic, $record->status, $record->status),
                    str_replace(["\n", ','], '', Arr::get($record->ext_info, 'name', '')),
                    str_replace(["\n", ','], '', Arr::get($record->ext_info, 'mobile', '')),
                    str_replace(["\n", ','], '', Arr::get($record->ext_info, 'addr', '')),
                ];
                fputcsv($file, $row);
            }
        });


        fclose($file);

        return $fname;
    }
}