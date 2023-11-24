<?php
//支付配置
return [
    'wechat' => [
        // 必填-商户号，服务商模式下为服务商商户号
        'mch_id' => '',
        'mch_secret' => '', //APIv3
        'mch_secret_v2' => '', //APIv2
        //必填-商户私钥 字符串或路径; 如：apiclient_key.pem
        'mch_cert' => app()->getRootPath() . 'data/cert/p_key.pem',
        //必填-商户公钥证书路径; 如：apiclient_cert.pem
        'mch_public_cert' => app()->getRootPath() . 'data/cert/p_cert.pem',
        'notify_url' => 'https://a.123.com/api/wechat/notify',
        // APPID，服务商模式下为服务商APPID
        'app_id' => '',
        // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
        'mch_cert_serial' => '',
        // 'wechat_cert_path' => [
        //     '' => ''
        // ],
        // 选填-服务商模式下，子 app 的 app_id(商户)
        'sub_app_id' => '',
        // 选填-服务商模式下，子商户id(商户)
        'sub_mch_id' => '',
        // 模式可选为： normal(商户), service(服务商)
        'mode' => 'normal'
    ]
];
