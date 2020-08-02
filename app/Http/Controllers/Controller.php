<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 返回固定格式的数据
     */
    public function result(int $errCode, string $errMsg = '', $data = [])
    {
        return [
            'error_code' => $errCode,
            'error_msg'  => $errMsg,
            'data' => $data,
        ];
    }
}
