<?php

namespace App\Admin\Controllers;

use App\Models\PrizeLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use App\Admin\Actions\Prize\Export;

class PrizeLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '获奖名单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PrizeLog());

        $grid->disableCreateButton(); // 不允许创建

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new Export());
        });

        // 查询过滤
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function($filter){
                $filter->between('id', 'ID');
                $filter->like('mobile', '手机号');
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal('activity_id', '所属活动')->select('selector/get-activity-options');
                $filter->equal('group_id', '所属奖品组')->select('selector/get-prize-group-options');
                $filter->between('created_at', '创建时间')->datetime();
            });
        });

        // 行显示条件
        $grid->model()->where('status', '<>', '-1')->orderBy('id', 'DESC');

        $grid->column('id', 'Id');
        $grid->column('activity.name', '所属活动');
        $grid->column('prizegroup.name', '奖品组');
        $grid->column('prize.name', '奖品');
        $grid->column('prize.type', '奖品类型')->display(function($type) {
            // 奖品类型，physical实物，coupon券，virtual虚拟奖
            $dic = ['physical' => '实物', 'coupon' => '券', 'virtual' => '虚拟奖'];
            return Arr::get($dic, $type, '');
        });
        $grid->column('source_type', '资格来源类型')->display(function($type) {
            $dic = ['' => '无', 'drive_reservation' => '预约试驾'];
            return Arr::get($dic, $type, '');
        });
        $grid->column('source_id', '来源Id');
        $grid->column('mobile', '用户手机号');
        $grid->column('status', '状态')->display(function($status) {
            $statusDic = [
                '0' => '<span style="color:red;">停用</span>',
                '1' => '<span style="color:green;">启用</span>',
            ];
            return Arr::get($statusDic, $status, '');
        });
        $grid->column('ext_info', '拓展信息');
        $grid->column('created_at', '创建时间')->date('Y-m-d H:i:s');

        // 操作
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView(false);
            $actions->disableEdit(true);
            $actions->disableDelete();
        });

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
        $model = PrizeLog::findOrFail($id);
        $show = new Show($model);

        $show->field('id', 'Id');
        $show->field('activity_id', '活动Id');
        $show->field('activity.name', '活动名称');
        $show->field('group_id', '奖品组Id');
        $show->field('prizegroup.name', '奖品组');
        $show->field('prize_id', '奖品Id');
        $show->field('prize.name', '奖品');
        $show->field('prize.type', '奖品类型')->as(function($type) {
            // 奖品类型，physical实物，coupon券，virtual虚拟奖
            $dic = ['physical' => '实物', 'coupon' => '券', 'virtual' => '虚拟奖'];
            return Arr::get($dic, $type, '');
        });
        $show->field('source_type', '资格来源类型')->as(function($type) {
            $dic = ['' => '无', 'drive_reservation' => '预约试驾'];
            return Arr::get($dic, $type, '');
        });
        $show->field('source_id', '来源Id');

        $show->field('code', '券码');
        $show->field('ip', 'Ip');
        $show->field('status', '状态')->as(function($status) {
            $statusDic = [
                '0' => '<span style="color:red;">作废</span>',
                '1' => '<span style="color:green;">有效</span>',
            ];
            return Arr::get($statusDic, $status, '');
        });
        $show->field('created_at', '创建时间');
        $show->field('updated_at', '更新时间');

        $show->divider();
        if($model->source_type == 'drive_reservation') {
            $show->field('ext_info.name', '收件人')->as(function($content) use ($model) {
                return $model->ext_info['name'];
            });
            $show->field('ext_info.mobile', '收件人手机号')->as(function($content) use ($model) {
                return $model->ext_info['mobile'];
            });
            $show->field('ext_info.addr', '收件地址')->as(function($content) use ($model) {
                return $model->ext_info['addr'];
            });
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PrizeLog());

        return $form;
    }
}
