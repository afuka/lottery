<?php

namespace App\Traits;

trait ErrConsoler
{
    protected $err = '';
    protected $errCode = '';

    public function setErr(int $errCode = 1, string $err)
    {
        $this->err = $err;
        $this->errCode = $errCode;
    }

    public function getErr()
    {
        return $this->err;
    }

    public function getErrCode()
    {
        return $this->errCode;
    }
}