<?php

namespace App\Service;

use App\Service\ErrEnum;
use Illuminate\Support\Arr;
use App\Traits\ErrConsoler;
use App\Repository\Sms\Alibaba;

class Sms
{
    use ErrConsoler;

    protected $handler = null;

    public function __construct()
    {
        $this->handler = new Alibaba();
    }

    /**
     * 发送短信
     */
    public function send($mobile, $code)
    {
        try {
            $this->handler->send($mobile, $code);
            return true;
        } catch (\Exception $e) {
            $this->setErr(ErrEnum::SERVICE_ERR, $e->getMessage());
            return false;
        }
    }
}