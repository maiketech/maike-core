<?php

namespace maike\interface;

interface SmsInterface
{
    public function send($mobile, $data);
}
