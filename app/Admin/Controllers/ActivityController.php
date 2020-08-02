<?php

namespace App\Admin\Controllers;

use App\Models\Activity;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

class ActivityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '活动管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Activity());

        // 查询过滤
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function($filter){
                $filter->between('id', 'ID');
                $filter->like('name', '活动名称');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('code', '活动代码');
                $filter->equal('status', '状态')->radio([
                    1 => '启用',
                    0 => '停用',
                ]);
            });
        });

        // 行显示条件
        $grid->model()->where('status', '<>', '-1')->orderBy('id', 'DESC');

        // 列字段
        $grid->column('id', 'ID');
        $grid->column('name', '名称');
        $grid->column('started', '开始时间');
        $grid->column('ended', '结束时间');
        $grid->column('status', '状态')->display(function($status) {
            $statusDic = [
                '0' => '<span style="color:red;">停用</span>',
                '1' => '<span style="color:green;">启用</span>',
            ];
            return Arr::get($statusDic, $status, '');
        });
        $grid->column('updated_at', '更新时间')->date('Y-m-d H:i:s');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Activity::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Activity());

        $form->text('name', '标题')->rules('required');
        $form->text('code', '活动代码')->rules('required');
        $form->textarea('bz', '描述')->rules('required');

        $form->datetime('started', '有效开始时间')->rules('required');
        $form->datetime('ended', '有效结束时间')->rules('required');
        $form->radio('status', '状态')->options([
            '0' => '停用', '1' => '启用'
        ])->default('1')->help('活动配置以key=>value形式配置，请在专人指导下配置');
        
        $form->keyValue('config', '活动配置');

        // 保存回调
        $form->saved(function (Form $form) {
            // 设置活动的缓存
            Redis::set('ACTIVITY_' . $form->model()->id, serialize($form->model()));
        });
        
        return $form;
    }
}
