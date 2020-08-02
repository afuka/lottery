<?php

namespace App\Admin\Controllers;

use App\Models\Prize;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

class PrizeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '奖品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Prize());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function($filter){
                $filter->between('id', 'ID');
                $filter->equal('group_id', '所属奖品组')->select('selector/get-prize-group-options');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('name', '奖品名称');
                $filter->equal('status', '状态')->radio([
                    1 => '启用',
                    0 => '停用',
                ]);
            });
        });

        // 行显示条件
        $grid->model()->where('status', '<>', '-1')->orderBy('id', 'DESC');

        $grid->column('id', 'Id');
        $grid->column('prizegroup.name', '所属奖品组');
        $grid->column('name', '奖品名称');
        $grid->column('type', '类型')->display(function($type) {
            // 奖品类型，physical实物，coupon券，virtual虚拟奖
            $dic = ['physical' => '实物', 'coupon' => '券', 'virtual' => '虚拟奖'];
            return Arr::get($dic, $type, '');
        });
        $grid->column('probability', '概率');
        $grid->column('is_default', '默认奖')->display(function($status) {
            $statusDic = [
                '0' => '<span style="color:red;">否</span>',
                '1' => '<span style="color:green;">是</span>',
            ];
            return Arr::get($statusDic, $status, '');
        });;
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
        $show = new Show(Prize::findOrFail($id));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Prize());

        $params = request()->route()->parameters();
        $id = Arr::get($params, 'prize', '0');
        if(!empty($id)) {
            $form->text('prizegroup.name', '所属奖品组')->readonly();
        } else {
            $form->select('group_id', '所属奖品组')->options('/admin/selector/get-prize-group-options')->rules('required');
        }
        
        $form->text('name', '奖品名称');
        $form->text('bz', '描述');
        $form->radio('type', '类型')->options([
            'physical' => '实物', 'coupon' => '券', 'virtual' => '虚拟奖'
        ])->default('physical');
        $form->number('total', '奖品总数');
        // $form->number('stock', __('Stock'));
        $form->number('probability', '中奖概率')->rules('max:10000|min:0')->help('中奖概率,中奖率:0-10000之间');
        $form->radio('is_default', '是否默认奖')->options([
            '0' => '否', '1' => '是'
        ])->default('0')->help('默认奖时，不会校验奖品总数是否发超');
        $form->radio('leave_info', '是否需要留资')->options([
            '0' => '否', '1' => '是'
        ])->default('0');
        $form->number('sort', '排序')->help('顺序从小到大');
        $form->image('image', '奖品图');
        $form->radio('status', '状态')->options([
            '0' => '停用', '1' => '启用'
        ])->default('1')->help('活动配置以key=>value形式配置，请在专人指导下配置');

        // $form->text('date_limit', '日期限制');
        $form->table('date_limit', '日期限制', function ($table) {
            $table->datetime('datetime', '日期');
            $table->number('num', '发放数')->default(0);
        });

        $form->keyValue('config', '活动配置');
        
        // 保存回调
        $form->saved(function (Form $form) {
            // 设置奖品组的缓存
            $prizes = Prize::where('status', '=', '1')->where('group_id', '=', $form->model()->group_id)->orderBy('sort', 'ASC')->get();
            Redis::set('PRIZE_GROUP_PRIZES_' . $form->model()->group_id, serialize($prizes));
        });

        return $form;
    }
}
