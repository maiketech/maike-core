<?php
// 短信配置
return [
    'default' => ['aliyun'], //默认网关

    'aliyun' => [
        'access_key_id' => '',
        'access_key_secret' => '',
        'sign_name' => '',
        'template' => [
            'smscode' => [
                'code' => 'SMS_4639904810',
                'content' => '您的验证码是79934',
            ]
        ]
    ],

    'qcloud' => [
        'sdk_app_id' => '', // 短信应用的 SDK APP ID
        'secret_id' => '', // SECRET ID
        'secret_key' => '', // SECRET KEY
        'sign_name' => '', // 短信签名
    ],
];
