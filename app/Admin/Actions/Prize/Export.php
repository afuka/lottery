<?php

namespace App\Admin\Actions\Prize;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class Export extends Action
{
    protected $selector = '.export';

    public function handle(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $type = 'Export';
        $name = '中奖名单导出';

        $params = [
            'type' => 'PrizeLog',
            'activity_id' => $request->get('activity_id', 0),
            'started' => $request->get('started', ''),
            'ended' => $request->get('ended', ''),
        ];

        try {
            $task = Task::create([
                'operator_id' => $user->id, 
                'type' => $type, 
                'name' => $name, 
                'memo' => $request->get('memo', ''), 
                'params' => $params,
            ]);
        } catch (\Exception $e) {
            return $this->response()->error('创建失败:' . $e->getMessage())->refresh();
        }

        return $this->response()->success('任务已创建「id:' . $task->id . '」,请移步任务列表查看进度')->refresh();
    }

    public function form()
    {
        $this->select('activity_id', '归属活动')->options('/selector/get-activity-options');
        $this->datetime('started', '开始时间(创建)')->rules('required');
        $this->datetime('ended', '结束时间(创建)')->rules('required');
        $this->text('memo', '备注说明，随便写点什么')->rules('required');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default export">导出</a>
HTML;
    }
}