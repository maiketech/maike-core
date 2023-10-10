<?php

namespace maike\interfaces;

interface PayInterface
{
    public function create($outTradeNo, $payMoney, $desc = '', $openid = '', $type = 'jsapi');

    public function notify();

    public function query($tradeNo);

    public function close($tradeNo);

    public function refund($data);

    public function queryRefund($refundNo);
}
