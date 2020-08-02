<?php

namespace App\Traits;

trait ErrConsoler
{
    protected $err = '';
    protected $errCode = '';

    public function setErr(string $err, int $errCode = 0)
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