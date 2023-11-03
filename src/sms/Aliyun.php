<?php

namespace maike\sms;

use maike\interface\SmsInterface;
use maike\trait\ErrorTrait;
use maike\util\StrUtil;
use Overtrue\EasySms\EasySms;

/**
 * 阿里云短信发送接口
 */
class Aliyun implements SmsInterface
{
    use ErrorTrait;

    protected $api = null;
    protected $config = [];
    protected $template = [];

    public function __construct($config = [])
    {
        $gateways = [];
        foreach ($config as $key => $item) {
            if ($key != 'default') {
                $gateways[$key] = $item;
                if (isset($item['template']) && count($item['template']) > 0) {
                    foreach ($item['template'] as $n => $tpl) {
                        $this->template[$n] = $tpl;
                    }
                }
            }
        }
        $this->config = [
            'timeout' => 5.0,
            'default' => [
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                'gateways' => $config['default'],
            ],
            'gateways' => [
                'errorlog' => [
                    'file' => $config['log'],
                ],
                ...$gateways
            ],
        ];
        $this->api = new EasySms($this->config);
    }

    /**
     * 发送
     *
     * @param string|array $mobile 手机号码
     * @param string $tpl 模板标识
     * @param array $params 短信内容参数
     * @return bool
     */
    public function send($mobile, $tpl, array $params = [])
    {
        if (empty($mobile) || empty($tpl)) {
            $this->setError("手机号码或模板ID无效");
            return false;
        }
        if (!is_array($mobile)) $mobile = [$mobile];
        if (count($mobile) > 50) {
            $this->setError("同时最多发送50条");
            return false;
        }
        if (!isset($this->template[$tpl])) {
            $this->setError("模板配置不存在");
            return false;
        }
        $tplData = $this->template[$tpl];
        $content = StrUtil::BatchReplace($tplData['content'], $params);
        $success = 0;
        foreach ($mobile as $m) {
            if ($this->api->send($m, [
                'content'  => $content,
                'template' => $tplData['code'],
                'data' => $params
            ])) {
                $success++;
            }
        }
        return $success > 0;
    }
}
