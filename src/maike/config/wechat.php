<?php
//微信小程序、公众号配置
return [
    //小程序相关配置
    'app' => [
        'app_id'  => 'wxe0805f59f031843c',         // AppID
        'secret'  => 'c8e4d0a677654f8329e0caec97468c62',     // AppSecret
        'token'   => '',          // Token
        'aes_key' => '',                    // EncodingAESKey

        'http' => [
            'throw'  => false, // 状态码非 200、300 时是否抛出异常，默认为开启
            'timeout' => 5.0,
            'retry' => true, // 使用默认重试配置
        ],
    ],

    //公众号相关配置
    'mp' => [
        'app_id'  => 'your-app-id',         // AppID
        'secret'  => 'your-app-secret',     // AppSecret
        'token'   => 'your-token',          // Token
        'aes_key' => '',                    // EncodingAESKey
    ]
];
