<?php

namespace App\Admin\Actions\Common;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

// 列表一行的
class RowDummyDelete extends RowAction
{
    public $name = '删除';

    public function handle(Model $model)
    {
        // 假删除
        $model->status = '-1';
        $model->save();

        return $this->response()->success('删除成功')->refresh();
    }

}