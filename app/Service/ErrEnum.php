<?php

namespace App\Service;

class ErrEnum
{
    const PARAM_ERR      = 1000000; // 参数错误
    const SERVICE_ERR    = 1000001; // 服务内部错误
    const CONSTRUCT_ERR  = 1000002; // 实体创建错误
    const NOT_EXISTS_ERR = 1000003; // 记录不存在错误
    const UNSUPPORTED    = 1000004; // 暂时不支持的功能
    const RUNTIME_ERR    = 1000005; // 运行时错误
    const AUTH_ERR       = 1000006; // 权限校验错误
    const DB_ERR         = 1000007; // 数据库操作错误
}