<?php

namespace maike\trait;

/**
 * 错误信息trait类
 * @package maike\trait
 */
trait ErrorTrait
{
    /**
     * 错误信息
     * @var string
     */
    protected $errorMessage = '';

    /**
     * 设置错误信息
     * @param string $error
     * @return bool
     */
    protected function setError(string $error): bool
    {
        $this->errorMessage = $error ?: '未知错误';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError(): string
    {
        return $this->errorMessage;
    }

    /**
     * 是否存在错误信息
     * @return bool
     */
    public function hasError(): bool
    {
        return !empty($this->errorMessage);
    }
}
