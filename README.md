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
php artisan storage:link
```


