<?php

namespace maike\services\wechat;

use think\Exception;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Log;
use maike\traits\ErrorTrait;
use maike\utils\Http;
use maike\utils\Json as JsonUtil;

/**
 * 微信基类
 */
class WechatBase
{
    use ErrorTrait;

    protected $config = null;
    protected $apiUrl = 'https://api.weixin.qq.com';

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * 
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     * @return array 解密后的原文
     */
    public function decode($encryptedData, $iv, $session_key)
    {
        $aesKey = base64_decode($session_key);
        if (strlen($iv) != 24) {
            $this->error = '参数错误';
            return false;
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result, true);
        if (!$dataObj || !is_array($dataObj)) {
            $this->error = '数据错误';
            return false;
        }
        if ($dataObj['watermark']['app_id'] != $this->config['app_id']) {
            $this->error = 'appid错误';
            return false;
        }
        return $dataObj;
    }

    /**
     * 获取access_token
     * @return mixed
     */
    protected function getAccessToken()
    {
        $cacheKey = $this->config['app_id'] . '@access_token';
        $token = Cache::get($cacheKey);
        if (!$token || empty($token)) {
            // 请求API获取 access_token
            $url = $this->apiUrl . "/cgi-bin/token";
            $response = Http::get($url, [
                'appid' => $this->config['app_id'],
                'secret' => $this->config['secret'],
                'grant_type' => 'client_credential'
            ]);
            if ($response && isset($response['status']) && isset($response['content'])) {
                $content = JsonUtil::Decode($response['content']);
                if ($response['status']['http_code'] == 200) {
                    // 写入缓存
                    $token = $content['access_token'];
                    $expiresIn = isset($content['expires_in']) && $content['expires_in'] > 0 ? $content['expires_in'] : 7000;
                    Cache::set($cacheKey, $token, $expiresIn);
                    return $token;
                } else {
                    $errorMsg = '获取access_token失败:' . $content['code'] . '|' . $content['message'];
                    $this->writeLog([
                        'app_id' => $this->config['app_id'],
                        'message' => $errorMsg,
                        'url' => $url,
                        'result' => $response
                    ], 'wechat');
                    $this->setError($errorMsg);
                    return false;
                }
            } else {
                $this->setError("获取access_token失败");
                return false;
            }
        }
        return $token;
    }

    /**
     * 清除AccessToken缓存
     *
     * @return bool
     */
    protected function clearAccessToken()
    {
        $cacheKey = $this->config['app_id'] . '@access_token';
        return Cache::delete($cacheKey);
    }

    /**
     * POST请求并返回结果
     *
     * @param string $url
     * @param array $params
     * @param array $header
     * @return mixed
     */
    protected function request($url, $params = [], $isPost = true, $header = null)
    {
        $url = $this->apiUrl . $url;
        if ($isPost) {
            $params = JsonUtil::Encode($params);
            $response = Http::post($url, $params, $header);
        } else {
            $response = Http::get($url, $params);
        }

        if ($response && isset($response['status']) && isset($response['content'])) {
            $content = JsonUtil::Decode($response['content']);
            if ($response['status']['http_code'] == 200) {
                if (!$content || (isset($content['errcode']) && $content['errcode'] != 0)) {
                    if ($content['errcode'] == 40001) $this->clearAccessToken();
                    $this->writeLog([
                        'appid' => $this->config['app_id'],
                        'message' => $content['errmsg'],
                        'url' => $url,
                        'result' => $content
                    ], 'wechat');
                    $this->setError($content['errmsg']);
                    return false;
                }
                return $content;
            } else {
                $errorMsg = '接口请求失败';
                $this->writeLog([
                    'app_id' => $this->config['app_id'],
                    'message' => $errorMsg,
                    'url' => $url,
                    'result' => $response
                ], 'wechat');
                $this->setError($errorMsg);
                return false;
            }
        }
        $this->setError("接口请求失败");
        return false;
    }

    /**
     * 写入日志 (使用tp自带驱动记录到runtime目录中)
     * @param $value
     * @param string $type
     */
    protected function writeLog($value, $type = "wechat")
    {
        $msg = is_string($value) ? $value : var_export($value, true);
        Log::write($msg, $type);
    }
}
