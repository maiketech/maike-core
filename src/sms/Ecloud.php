<?php

namespace maike\service\sms;

use maike\interface\SmsInterface;

/**
 * 移动云短信发送接口
 */
class Ecloud implements SmsInterface
{
    public function send($mobile, $data)
    {

        return true;
    }
}
