<?php

namespace maike\services\pay;

use think\Exception;
use think\facade\Cache;
use maike\interfaces\PayInterface;
use maike\utils\JsonUtil;
use maike\utils\HttpUtil;

/**
 * 微信支付类
 */
class Wechat extends PayBase implements PayInterface
{
    protected $apiUrl = 'https://api.mch.weixin.qq.com';
    protected $config = [
        'mch_id' => '',
        'mch_secret_key_v2' => '',
        'mch_secret_key' => '',
        'mch_cert_serial' => '',
        'mch_cert' => '',
        'mch_cert_key' => '',
        'wechat_cert_serial' => '',
        'notify_url' => '',
        'mp_app_id' => '',
        'mini_app_id' => ''
    ];
    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 微信小程序支付
     *
     * @param array $data
     * @return array
     */
    public function create($data)
    {
        $types = [
            'h5'     => '/v3/pay/transactions/h5',
            'app'    => '/v3/pay/transactions/app',
            'jsapi'  => '/v3/pay/transactions/jsapi',
            'native' => '/v3/pay/transactions/native',
        ];
        $order = [
            'appid' => $this->config['mini_app_id'],
            'mchid' => $this->config['mch_id'],
            'out_trade_no' => $data['out_trade_no'],
            'description' => empty($desc) ? $data['out_trade_no'] . '付款' : $desc,
            'amount' => [
                'total' => intval($payMoney * 100),
            ],
            'payer' => [
                'openid' => $openid,
            ],
            "notify_url" => $this->config['notify_url']
        ];
        $result = $this->request('POST', $types[$type], $order);

        if (!$result) return false;

        // 支付参数签名
        $time = strval(time());
        $appid = $this->config['mini_app_id'];
        $nonceStr = self::createNoncestr();
        if ($type === 'app') {
            $sign = $this->buildDataSign(join("\n", [$appid, $time, $nonceStr, $result['prepay_id'], '']));
            return ['partnerId' => $this->config['mch_id'], 'prepayId' => $result['prepay_id'], 'package' => 'Sign=WXPay', 'nonceStr' => $nonceStr, 'timeStamp' => $time, 'sign' => $sign];
        } elseif ($type === 'jsapi') {
            $sign = $this->buildDataSign(join("\n", [$appid, $time, $nonceStr, "prepay_id={$result['prepay_id']}", '']));
            return ['appId' => $appid, 'timestamp' => $time, 'timeStamp' => $time, 'nonceStr' => $nonceStr, 'package' => "prepay_id={$result['prepay_id']}", 'signType' => 'RSA', 'paySign' => $sign];
        } else {
            return $result;
        }
    }

    /**
     * 验证并返回通知信息
     *
     * @return array
     */
    public function notify()
    {
        if (empty($data)) {
            $data = json_decode($this->getRawInput(), true);
        }
        if (isset($data['resource'])) {
            $data['result'] = $this->decryptToString(
                $data['resource']['associated_data'],
                $data['resource']['nonce'],
                $data['resource']['ciphertext']
            );
        }
        return $data;
    }

    /**
     * 查询订单
     *
     * @param string $tradeNo
     * @return array|false
     */
    public function query($tradeNo)
    {
        $path = "/v3/pay/transactions/out-trade-no/{$tradeNo}";
        return $this->request('GET', "{$path}?mchid={$this->config['mch_id']}");
    }

    /**
     * 关闭订单
     *
     * @param string $tradeNo
     * @return array|false
     */
    public function close($tradeNo)
    {
        $data = ['mchid' => $this->config['mch_id']];
        $path = "/v3/pay/transactions/out-trade-no/{$tradeNo}/close";
        return $this->request('POST', $path, $data);
    }

    /**
     * 创建退款单
     *
     * @param array $data
     * @return array|false
     */
    public function refund($data)
    {
        $path = '/v3/refund/domestic/refunds';
        return $this->request('POST', $path, $data);
    }

    /**
     * 查询退款单
     *
     * @param string $refundNo
     * @return array|false
     */
    public function queryRefund($refundNo)
    {
        $path = "/v3/refund/domestic/refunds/{$refundNo}";
        return $this->request('GET', $path);
    }

    /**
     * 接口请求
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     */
    public function request($method, $url, $data = [], $headers = [])
    {
        $method = strtoupper($method);
        $dataStr = JsonUtil::Encode($data);
        list($time, $nonce) = [time(), uniqid() . rand(1000, 9999)];
        $signstr = join("\n", [$method, $url, $time, $nonce, $dataStr, '']);

        // 生成数据签名TOKEN
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->config['mch_id'],
            $nonce,
            $time,
            $this->config['mch_cert_serial'],
            $this->buildDataSign($signstr)
        );
        $url = $this->apiUrl . $url;

        $headers = array_merge([
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: https://maike-tech.com',
            "Authorization: WECHATPAY2-SHA256-RSA2048 {$token}",
            // "Wechatpay-Serial: {$this->config['wechat_cert_serial']}"
        ], $headers);

        $res = false;
        if ($method == 'POST') {
            $data = JsonUtil::Encode($data);
            $res = HttpUtil::post($url, $data, $headers);
        } else {
            $res = HttpUtil::get($url, $data, $headers);
        }

        if ($res && isset($res['status']) && isset($res['content'])) {
            $content = JsonUtil::Decode($res['content']);
            if ($res['status']['http_code'] == 200) {
                return $content;
            } else {
                $this->setError($content['message']);
                return false;
            }
        }
        return false;
    }

    /**
     * 生成数据签名
     * 
     * @param string $data 签名内容
     * @return string
     */
    protected function buildDataSign($data)
    {
        if (file_exists($this->config['mch_cert_key'])) {
            $mch_cert_key = file_get_contents($this->config['mch_cert_key']);
        } else {
            $mch_cert_key = $this->config['mch_cert_key'];
        }
        $pkeyid = openssl_pkey_get_private($mch_cert_key);
        openssl_sign($data, $signature, $pkeyid, 'sha256WithRSAEncryption');
        return base64_encode($signature);
    }

    /**
     * 验证内容签名
     * 
     * @param string $data 签名内容
     * @param string $sign 原签名值
     * @param string $serial 证书序号
     * @return int
     */
    protected function signVerify($data, $sign, $serial)
    {
        $cert = $this->getWechatCert($serial);
        return @openssl_verify($data, base64_decode($sign), openssl_x509_read($cert), 'sha256WithRSAEncryption');
    }

    /**
     * 获取平台证书
     * @param string $serial
     * @return string
     */
    protected function getWechatCert($serial = '')
    {
        $cacheKey = "{$this->config['mch_id']}_certs";
        $certs = Cache::get($cacheKey);
        if (empty($certs) || empty($certs[$serial]['content'])) {
            //下载平台证书
            $result = $this->request('GET', '/v3/certificates');
            $certs = [];
            foreach ($result['data'] as $vo) {
                $certs[$vo['serial_no']] = [
                    'expire'  => strtotime($vo['expire_time']),
                    'content' => $this->decryptToString(
                        $vo['encrypt_certificate']['associated_data'],
                        $vo['encrypt_certificate']['nonce'],
                        $vo['encrypt_certificate']['ciphertext']
                    )
                ];
            }
            Cache::set("{$this->config['mch_id']}_certs", json_encode([$certs], JSON_UNESCAPED_UNICODE));
        }
        if (empty($certs[$serial]['content']) || $certs[$serial]['expire'] < time()) {
            throw new Exception("读取平台证书失败！");
        } else {
            return $certs[$serial]['content'];
        }
    }

    /**
     * 平台证书解密
     */
    public function decryptToString($associatedData, $nonceStr, $ciphertext)
    {
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }
        try {
            // ext-sodium (default installed on >= PHP 7.2)
            if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
                return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->config['mch_secret_key']);
            }
            // ext-libsodium (need install libsodium-php 1.x via pecl)
            if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
                return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->config['mch_secret_key']);
            }
            // openssl (PHP >= 7.1 support AEAD)
            if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
                $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
                $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);
                return \openssl_decrypt($ctext, 'aes-256-gcm', $this->config['mch_secret_key'], \OPENSSL_RAW_DATA, $nonceStr, $authTag, $associatedData);
            }
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        } catch (\SodiumException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        throw new Exception('AEAD_AES_256_GCM 需要 PHP 7.1 以上或者安装 libsodium-php');
    }
}
