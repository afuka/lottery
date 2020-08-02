<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('home');
    
    $router->resource('activities', ActivityController::class);// 活动管理
    $router->resource('tasks', TaskController::class); // 任务管理
    $router->resource('dealers', DealerController::class); // 经销商管理
    $router->resource('drive-reservations', DriveReservationController::class); // 预约试驾记录
    $router->resource('prize-groups', PrizeGroupController::class); // 奖品组管理
    $router->resource('prizes', PrizeController::class); // 奖品管理
    $router->resource('prize-logs', PrizeLogController::class); // 中奖人员信息

    // 接口
    $router->get('/selector/get-activity-options', 'SelectorController@getActivityOption');
    $router->get('/selector/get-prize-group-options', 'SelectorController@getPrizeGroupOption');
});
