<?php

namespace App\Admin\Controllers;

use App\Models\DriveReservation;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\DriveReservation\Export;
use Illuminate\Support\Arr;

class DriveReservationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '预约试驾';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DriveReservation());

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
                $filter->between('created_at', '创建时间')->datetime();
            });
        });

        // 行显示条件
        $grid->model()->where('status', '<>', '-1')->orderBy('id', 'DESC');

        $grid->column('id', 'Id');
        $grid->column('activity.name', '归属活动');
        $grid->column('source', '来源')->help('同一活动可能不同来源，唯一识别标识为 活动+来源+手机号')->hide();
        $grid->column('mobile', '手机号');
        $grid->column('name', '姓名');
        $grid->column('province', '省');
        $grid->column('city', '市');
        $grid->column('dealer_code', '经销商代码');
        $grid->column('dealer', '经销商');
        $grid->column('crm_sync', '同步CRM')->display(function($status) {
            $dic = [// 是否需要同步到crm
                '0' => '否', '1' => '需要同步',
            ];
            return Arr::get($dic, $status, '');
        })->hide();
        $grid->column('sync_status', '同步状态')->display(function($status) {
            $dic = [// 同步到crm
                '0' => '待推送', '1' => '成功', '-1' => '推送失败'
            ];
            return Arr::get($dic, $status, '');
        })->hide();
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
        $show = new Show(DriveReservation::findOrFail($id));

        $show->field('id', 'Id');
        $show->field('activity_id', '活动Id');
        $show->field('activity.name', '活动名称');
        $show->field('source', '来源');
        $show->field('name', '姓名');
        $show->field('mobile', '手机号');
        $show->field('gender', '性别')->display(function($gender) {
            $dic = [
                '0' => '未知', '1' => '男', '2' => '女'
            ];
            return $dic[$gender];
        });
        $show->field('province', '省');
        $show->field('city', '市');
        $show->field('dealer_code', '经销商代码');
        $show->field('dealer', '经销商');
        $show->field('media', '媒体来源');
        $show->field('ip', 'Ip');
        $show->field('crm_sync', '同步CRM')->display(function($status) {
            $dic = [
                '0' => '否', '1' => '需要同步',
            ];
            return Arr::get($dic, $status, '');
        });
        $show->field('crm_sync', 'Crm 同步状态')->display(function($status) {
            $dic = [
                '0' => '待推送', '1' => '成功', '-1' => '推送失败'
            ];
            return Arr::get($dic, $status, '');
        });
        $show->field('ordertime', '预约试驾时间')->datetime();
        $show->field('buytime', '预计购买时间')->datetime();
        $show->field('created_at', '创建时间')->datetime();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DriveReservation());

        return $form;
    }
}
