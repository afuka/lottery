<?php

namespace App\Service;

use App\Models\Task;
use App\Traits\ErrConsoler;

/**
 * 消费掉 admin_task 中的消息, admin_task 主要是管理员操作的异步任务
 */
class TaskConsumer
{
    use ErrConsoler;

    protected $repositories = [];

    /**
     * 执行消息
     */
    public function consume(Task $task)
    {
        if(!isset($this->repositories[$task->type])) {
            try {
                $class = 'App\\Repository\\TaskConsumer\\' . $task->type;
                $consomer = new $class();
            } catch (\Exception $e) {
                $this->setErr($e->getMessage(), ErrEnum::SERVICE_ERR);
                return false;
            }
            $this->repositories[$task->type] = $consomer;
        } else {
            $consomer = $this->repositories[$task->type];
        }
        // 任务开始
        $task->status = '1';
        $task->save();
        
        $success = false;
        try {
            $success = $consomer->consume($task);
        } catch (\Exception $e) {
            // 抛出异常就失败了
            $task->result = $e->getMessage();
            $task->status = '-1';
            $task->save();

            $this->setErr($e->getMessage(), ErrEnum::RUNTIME_ERR);
            return false;
        }

        return $success;
    }
}