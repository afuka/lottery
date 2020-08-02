# 简易营销互动活动注意事项

1. 为了效率，将路由和配置均生成缓存

```
# 相关建立
php artisan config:cache 
php artisan route:cache 
php artisan package:discover
php artisan event:cache
php artisan view:cache

# 相关清理
php artisan cache:clear
php artisan config:clear
php artisan event:clear
php artisan optimize:clear
php artisan route:clear
php artisan view:clear 
```


2. 配置资源文件可对外访问

```
# 建立软连接
php artisan storage:link
# 缓存文件生成位置
/storage/app/public/caches
# 访问方式
域名/storage/caches/dealer.js
```


3. 后台开发

```
# 生成数据库迁移脚本, 注意，生成的时候，最好表名带个s，否则模型指定的时候，需要设置表名
php artisan make:migration create_activities_table
php artisan migrate

# 创建模型，其中模型都在Models目录下，注意，命名时不带 s
php artisan make:model Models\\Activity

# 创建控制器，会按照字段自动生成代码，并根据控制台提示，添加响应路由到route.php
php artisan admin:make ActivityController --model App\\Models\\Activity

# 创建列表左上侧按钮
php artisan admin:action Common\\SyncXX --name="什么东西"
```

4. 命令脚本
```
# 同步经销商 并 生成js文件缓存,其中使用的驱动，在代码中
php artisan command:sync-dealers
```

5. 公共函数位置
```
app\Helpers
```

6. 缓存的key
```
# 活动的key
ACTIVITY_{活动Id}   存储内容，序列化后的活动对象, 需要unserialize下
DRIVE_RESERVATION_EXISTS_{活动Id}_{source来源组}_{手机号}    唯一识别标识为是否已经留资过 活动+来源+手机
PRIZE_GROUP_{奖品组Id}   存储奖品组的内容,序列化后的活动对象, 需要unserialize下
PRIZE_GROUP_PRIZES_{奖品组Id}  存储奖品组中奖品的内容,序列化后的活动对象, 需要unserialize下
```

