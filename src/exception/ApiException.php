<?php

namespace maike\exception;

/**
 * API应用错误信息
 * @package maike\exception
 */
class ApiException extends \RuntimeException
{
    // 状态码
    public $code;

    // 错误信息
    public $msg = '';

    // 输出的数据
    public $data = [];

    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $message = $errInfo[1] ?? '未知错误';
            if ($code === 0) {
                $code = $errInfo[0] ?? 0;
            }
        }

        parent::__construct($message, $code, $previous);

        $this->code = $code ?? 0;
        $this->msg = $message ?? '很抱歉，服务器内部错误';
        $this->data = [];
    }
}
