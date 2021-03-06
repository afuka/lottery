// TODO:
1. 后台记录删除时候同时删除key
2. Sms 服务的重构，维护一个消息模板
3. 调查问卷的
4. 可视化页面生成工具
5. 会员模块，手机号登录/第三方授权登录
6. 奖品组需要一个单独限制单个抽奖组抽奖时间，相当于活动时间 + 奖品组时间作为限制
7. 奖品组中奖概率展示需要进行调整，当前中奖概率容易产生误解

# 简易营销互动活动注意事项

部署过程
```
git clone https://github.com/afuka/lottery.git 项目目录
cd 项目目录
chown -R nobody:nobody storage   其中 nobody 是 nginx 用户，根据配置变动
wget https://getcomposer.org/download/1.10.10/composer.phar   如果没有安装 composer 的话
/usr/local/php7/bin/php composer.phar install  或者 composer install
cp .env.example .env
/usr/local/php7/bin/php artisan key:generate
编辑 .env 中的配置
/usr/local/php7/bin/php artisan storage:link
```

1. 为了效率，将路由和配置均生成缓存

```
# 相关建立
/usr/local/php7/bin/php artisan config:cache 
/usr/local/php7/bin/php artisan route:cache 
/usr/local/php7/bin/php artisan package:discover
/usr/local/php7/bin/php artisan event:cache
/usr/local/php7/bin/php artisan view:cache

# 相关清理
/usr/local/php7/bin/php artisan cache:clear
/usr/local/php7/bin/php artisan config:clear
/usr/local/php7/bin/php artisan event:clear
/usr/local/php7/bin/php artisan optimize:clear
/usr/local/php7/bin/php artisan route:clear
/usr/local/php7/bin/php artisan view:clear 
```


2. 配置资源文件可对外访问

```
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
# 将奖券入库
php artisan command:generate-prize-tickets-pool
# 消费后台消息
php artisan command:comsume-task
# 同步销售线索
php artisan command:push-drive-reservations-to-crm
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
DRIVE_RESERVATION_INFO_{记录Id}      缓存这个提交记录的部分主要信息, 有过期时间

PRIZE_GROUP_{奖品组Id}   存储奖品组的内容,序列化后的活动对象, 需要unserialize下
PRIZE_GROUP_PRIZES_{奖品组Id}  存储奖品组中奖品的内容,序列化后的活动对象, 需要unserialize下

LOTTERY_TIMES_LIMIT_{奖品组Id}_{资格来源}_{记录Id}  计数器，当前多少次了
LOTTERY_GROUP_LOCK_{奖品组Id}  奖品组抽奖锁
LOTTERY_PRIZE_SEND_COUNT_{奖品Id}  计数器，记录当前发出去多少个这个商品了
LOTTERY_PRIZE_GET_USER_LIMIT_BY_GROUP_{奖品组Id}_{唯一识别值}   计数器，记录这个唯一识别的值中奖几次
LOTTERY_PRIZE_GET_USER_LIMIT_BY_ACTIVITY_{活动Id}_{唯一识别值}   计数器，记录这个唯一识别的值中奖几次
LOTTERY_PRIZE_TICKETS_POOL_{奖品Id}  有序集合，来存储奖券的所有的券码
LOTTERY_PRIZE_TICKETS_ALIVE_{奖品Id}  有序集合，来存储奖券剩余的券码
LOTTERY_USER_GET_PRIZE_LOG_{获奖记录日志Id}   缓存数据，到时候校验的时候用, 有过期时间
LOTTERY_LEAVE_INFO_FLAG_{获奖记录日志Id}   判断是否已经留资了
```

