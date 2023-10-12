<?php

namespace maike\services\wechat;

use think\facade\Config;

/**
 * 微信小程序类
 */
class WechatApp extends WechatBase
{
    static $instance;

    public static function init($config = [])
    {
        if (self::$instance === null) {
            if (!$config || count($config) < 1) {
                $config = Config::get("wechat.app");
            }
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * 小程序登录（jscode2session）
     *
     * @param string $code
     * @return mixed
     */
    public function getSession($code)
    {
        $result = $this->request('/sns/jscode2session', [
            'appid' => $this->config['app_id'],
            'secret' => $this->config['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ], false);
        return $result;
    }

    public function getMobile($code)
    {
        $result = $this->request('/wxa/business/getuserphonenumber?access_token=' . $this->getAccessToken(), [
            'code' => $code
        ]);
        return $result;
    }

    public function getWxacode($scene, $page = '',  $width = 500)
    {
        $params = [
            'scene' => $scene,
            'width' => $width
        ];
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $result = $this->request('/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken(), $params);
        return $result;
    }

    public function getQrcode($path, $width = 500)
    {
        $params = [
            'path' => $path,
            'width' => $width
        ];
        $result = $this->request('/cgi-bin/wxaapp/createwxaqrcode?access_token=' . $this->getAccessToken(), $params);
        return $result;
    }
}
