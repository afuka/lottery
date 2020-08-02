<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// /api/activity/{$activity_id}
Route::group([
    'prefix' => '/activity/{activityId?}',
    'middleware' => ['verify.activity'],
], function(){
    Route::any('/reserve-drive/create', 'DriveReservationController@create');  // 填写预约试驾信息

    Route::any('/lottery/prizes', 'LotteryController@prizes');  // 获取奖品组奖品
    Route::any('/lottery/draw', 'LotteryController@draw');  // 抽奖
    Route::any('/lottery/leave-info', 'LotteryController@leaveInfo');  // 中奖留资
});