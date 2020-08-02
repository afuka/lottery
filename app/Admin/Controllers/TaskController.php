<?php

namespace App\Admin\Controllers;

use App\Models\Task;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class TaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '任务列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Task());

        // 头部的按钮禁用
        $grid->disableCreateButton(); // 禁用创建

        // 行显示条件
        $user = Auth::guard('admin')->user();
        $grid->model()->where('operator_id', $user->id)->orderBy('id', 'DESC');

        $grid->column('id', '任务Id');
        $grid->column('name', '任务名称');
        $grid->column('type', '驱动类型');
        $grid->column('memo', '任务描述');
        $grid->column('status', '执行状态')->display(function ($status) {
            // -1终止任务；0等待执行；1进行中；2已完成;3挂起
            $dic = [
                '-1' => '<span style="color:red;">终止任务</span>',
                '0' => '<span style="color:red;">等待执行</span>',
                '1' => '<span style="color:yellow;">进行中</span>',
                '2' => '<span style="color:green;">已完成</span>',
                '3' => '<span style="color:yellow;">挂起</span>',
            ];
            return Arr::get($dic, $status, '');
        });
        $grid->column('result', '执行结果')->display(function($result) {
            if(strpos($result, '.csv') !== false) {
                return '<a href="' . config('filesystems.disks.admin.url') . $result . '" target="_blank">点击下载</a>';
            }
            return $result;
        });
        $grid->column('updated_at', '更新时间')->date('Y-m-d H:i:s');
        $grid->column('created_at', '创建时间')->date('Y-m-d H:i:s');

        // 全部关闭
        $grid->disableActions();
        // 去掉批量操作
        $grid->disableBatchActions();

        return $grid;
    }
}
