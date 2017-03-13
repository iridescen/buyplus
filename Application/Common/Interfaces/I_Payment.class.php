<?php

namespace Common\Interfaces;

interface I_Payment
{

    public function title();
    public function key();

    // 发出支付请求
    public function pay();
    // 获取支付结果
    public function result();
}