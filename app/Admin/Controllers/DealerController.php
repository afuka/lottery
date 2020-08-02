<?php

namespace App\Admin\Controllers;

use App\Models\Dealer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;

class DealerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '经销商';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Dealer());

        // 查询过滤
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function($filter){
                $filter->between('id', 'ID');
                $filter->like('name', '经销商');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('code', '经销商代码');
                $filter->equal('status', '状态')->radio([
                    1 => '启用',
                    0 => '停用',
                ]);
            });
        });

        $grid->column('id', 'Id');
        $grid->column('province', '省');
        $grid->column('city', '市');
        $grid->column('code', '代码');
        $grid->column('name', '全称');
        $grid->column('simplify', '简称');
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
        $show = new Show(Dealer::findOrFail($id));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Dealer());

        $form->text('code', '代码')->rules('required');
        $form->text('name', '全称')->rules('required');
        $form->text('simplify', '简称')->rules('required');
        $form->radio('type', '经销商类型')->options([
            '' => '无', 'sales' => '销售'
        ])->default('');
        $form->text('province', '省')->rules('required');
        $form->text('city', '市')->rules('required');
        $form->text('addr', '详细地址');
        $form->text('tel', '联系电话');
        $form->text('supports', '支持')->help('支持类型，多个请用英文半角字符分割');
        $form->radio('status', '状态')->options([
            '0' => '停用', '1' => '启用'
        ])->default('1');

        return $form;
    }
}
