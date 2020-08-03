<?php

namespace App\Repository\Exports;

use Illuminate\Support\Arr;
use App\Models\DriveReservation as DriveReservationModel;


/**
 * 导出
 */
class DriveReservation
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
            if(!is_dir('./storage/app/public/exports/driveseservation/' . date('Y/md'))) {
                mkdir('./storage/app/public/exports/driveseservation/' . date('Y/md'), 0777, true);
            }
            // 创建文件
            $fname = '/exports/driveseservation/' . date('Y/md/') . time() . mt_rand(10000, 99999) . '.csv';
            $file = fopen('./storage/app/public' . $fname, 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));
        } catch (\Exception $e) {
            throw new \Exception('文件生成失败' . $e->getMessage(), 1);
        }

        // 头
        $header = [
            'Id', '归属活动Id', '归属活动', '来源标识', '姓名', '手机号', '性别', 
            '省', '市', '经销商代码', '经销商', '车型', 'IP', '媒体', '预约试驾时间', 
            '预计购买时间', '创建时间', '更新时间', '状态'
        ];
        fputcsv($file, $header);

        $statusDic = ['-1' => '已删除', '0' => '删除' ,'1' => '正常'];
        $genderDic = ['0' => '未知', '1' => '男' ,'2' => '女'];

        $model = DriveReservationModel::with('activity')->whereBetween('created_at', [$started, $ended]);
        if(!empty($activityId)) $model->where('activity_id', $activityId);

        $records = $model->chunk(200, function($records) use ($file, $statusDic, $genderDic) {
            foreach($records as $record) {
                $row = [
                    $record->id,
                    $record->activity_id,
                    $record->activity->name,
                    $record->source,
                    str_replace(["\n", ','], '', $record->name),
                    $record->mobile,
                    Arr::get($genderDic, $record->gender, $record->gender),
                    $record->province,
                    $record->city,
                    $record->dealer_code,
                    $record->dealer,
                    $record->car,
                    $record->ip,
                    $record->media,
                    $record->ordertime,
                    $record->buytime,
                    $record->created_at,
                    $record->updated_at,
                    Arr::get($statusDic, $record->status, $record->status),
                ];
                fputcsv($file, $row);
            }
        });


        fclose($file);

        return $fname;
    }
}