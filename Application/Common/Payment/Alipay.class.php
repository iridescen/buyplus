<?php

namespace Common\Payment;

use Common\Interfaces\I_Payment;

class Alipay implements I_Payment
{
    public function title()
    {
        return '支付宝';

    }
    public function key()
    {
        return basename(__CLASS__);
    }

    // 发出支付请求
    public function pay()
    {

    }
    // 获取支付结果
    public function result()
    {

    }
}