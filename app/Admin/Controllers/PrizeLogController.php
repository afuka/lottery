<?php

namespace App\Admin\Controllers;

use App\Models\PrizeLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PrizeLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PrizeLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PrizeLog());

        $grid->column('id', __('Id'));
        $grid->column('activity_id', __('Activity id'));
        $grid->column('group_id', __('Group id'));
        $grid->column('prize_id', __('Prize id'));
        $grid->column('source_type', __('Source type'));
        $grid->column('source_id', __('Source id'));
        $grid->column('code', __('Code'));
        $grid->column('ip', __('Ip'));
        $grid->column('status', __('Status'));
        $grid->column('ext_info', __('Ext info'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(PrizeLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('activity_id', __('Activity id'));
        $show->field('group_id', __('Group id'));
        $show->field('prize_id', __('Prize id'));
        $show->field('source_type', __('Source type'));
        $show->field('source_id', __('Source id'));
        $show->field('code', __('Code'));
        $show->field('ip', __('Ip'));
        $show->field('status', __('Status'));
        $show->field('ext_info', __('Ext info'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

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
