<?php

namespace App\Repository\TaskConsumer;

use App\Models;
use Illuminate\Support\Arr;

/**
 * 导出
 */
class Export
{
    public function consume(Models\Task $task)
    {
        $params = $task->params;
        $exportType = Arr::get($params, 'type', '');
        if(empty($exportType)) {
            throw new \Exception('未指定导出驱动信息', 1);
        }

        // 创建exporter
        try {
            $class = 'App\\Repository\\Exports\\' . $exportType;
            $exporter = new $class();
        } catch (\Exception $e) {
            throw new \Exception('不存在的导出驱动', 1);
        }

        try {
            $result = $exporter->run($task->params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }

        // 任务结束
        $task->result = $result;
        $task->status = '2';
        $task->save();
    }
}