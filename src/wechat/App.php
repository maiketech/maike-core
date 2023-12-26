<?php

namespace maike\wechat;

use think\facade\Config;

/**
 * 微信小程序类
 */
class App extends WechatBase
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

    /**
     * 发送订阅消息
     *
     * @param string $tplId 模板ID
     * @param string $touser 接收者openid
     * @param array $data   参数
     * @param string $page  跳转页面路径
     * @return boolean
     * @example 
     * $tplId = 'xxxxxx';
     * $touser = 'xxxxxx';
     * $data = [
     *     'keyword1' => ['value' => '123456'],
     *     'keyword2' => ['value' => '123456'],
     * ];
     * $page = '/pages/index/index';
     * $res = $app->sendMessage($tplId, $touser, $data, $page);
     */
    public function sendMessage($tplId, $touser, $data, $page = '')
    {
        $params = [
            'template_id' => $tplId,
            'touser' => $touser,
            'data' => $data,
            'miniprogram_state' => 'formal',
            'lang' => 'zh_CN'
        ];
        if (!empty($page)) {
            $params['page'] = $page;
        }
        $result = $this->request('/cgi-bin/message/subscribe/send?access_token=' . $this->getAccessToken(), $params);
        return $result;
    }

    /**
     * 获取手机号码
     *
     * @param string $code
     * @return array
     */
    public function getMobile($code)
    {
        $result = $this->request('/wxa/business/getuserphonenumber?access_token=' . $this->getAccessToken(), [
            'code' => $code
        ]);
        return $result && isset($result['phone_info']) ? $result['phone_info'] : false;
    }

    /**
     * 生成小程序码
     *
     * @param string $scene 场景参数
     * @param string $page 跳转页面
     * @param integer $width 二维码的宽度
     * @return void
     */
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

    /**
     * 生成小程序二维码
     *
     * @param string $path 跳转页面
     * @param integer $width 二维码的宽度
     * @return void
     */
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
