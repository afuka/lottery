<?php

namespace App\Admin\Controllers;

use App\Models\PrizeGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

class PrizeGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '奖品组';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PrizeGroup());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function($filter){
                $filter->between('id', 'ID');
                $filter->equal('activity_id', '所属活动')->select('selector/get-activity-options');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('name', '奖品组名称');
                $filter->equal('status', '状态')->radio([
                    1 => '启用',
                    0 => '停用',
                ]);
            });
        });

        // 行显示条件
        $grid->model()->where('status', '<>', '-1')->orderBy('id', 'DESC');

        $grid->column('id', 'Id')->display(function($id) {
            return $id . '「' . base64_encode($id) . '」';
        });
        $grid->column('activity.name', '所属活动');
        $grid->column('name', '奖品组');
        $grid->column('user_limit_mode', '用户限制')->display(function($limit) {
            // 用户中奖限制，no 不限制, once_per_group 每个奖品组一次, once_per_activity 每个活动一次
            $dic = ['no' => '不限制', 'once_per_group' => '每个奖品组一次', 'once_per_activity' => '每个活动一次'];
            return Arr::get($dic, $limit, '');

        });
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
        $show = new Show(PrizeGroup::findOrFail($id));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PrizeGroup());

        $form->select('activity_id', '所属活动')->options('/admin/selector/get-activity-options')->rules('required');
        $form->text('name', '标题')->rules('required');
        $form->textarea('bz', '描述')->rules('required');

        $form->radio('user_limit_mode', '中奖限制')->options([
            'no' => '不限制', 'once_per_group' => '每个奖品组一次', 'once_per_activity' => '每个活动一次'
        ])->default('no')->help('注意该限制只作用域设置生效之后的数据,若之前没设置时产生的数据不计入限制');

        $form->radio('status', '状态')->options([
            '0' => '停用', '1' => '启用'
        ])->default('1')->help('活动配置以key=>value形式配置，请在专人指导下配置');
        
        /**
         * config 的 配置说明
         *      source_type 限制抽奖资格获取来源
         *      limit_times 限制抽奖资格次数
         */
        $form->embeds('config', '奖品组配置', function($form) {
            $form->radio('source_type', '限制抽奖资格获取来源')->options([
                'drive_reservation' => '预约试驾'
            ])->default('source_type');
            $form->number('limit_times', '可抽奖次数')->default(1);
        });

        // 保存回调
        $form->saved(function (Form $form) {
            // 设置奖品组的缓存
            Redis::set('PRIZE_GROUP_' . $form->model()->id, serialize($form->model()));
        });

        return $form;
    }
}
