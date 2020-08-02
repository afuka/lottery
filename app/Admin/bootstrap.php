<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);

use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Show;
use App\Admin\Actions\Common\RowDummyDelete;

Grid::init(function (Grid $grid) {
    $grid->disableExport();
    $grid->batchActions(function ($batch) {
        $batch->disableDelete();
    });
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->add(new RowDummyDelete);
        $actions->disableView();
        $actions->disableDelete();
    });
});

Show::init(function (Show $show) {
    $show->panel()->tools(function (Show\Tools $tools) {
        $tools->disableEdit();
        $tools->disableDelete();
    });
});

Form::init(function (Form $form) {
    $form->disableViewCheck();
    $form->disableEditingCheck(false);
    $form->disableCreatingCheck();
    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
    });
});