<?php

namespace maike\traits;

use think\facade\Queue as ThinkQueue;

/**
 * 快捷加入消息队列
 * @package maike\traits
 */
trait QueueTrait
{
    /**
     * 加入队列
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatch(array $data = [], ?string $action = '', ?string $queueName = null)
    {
        $className = __CLASS__;
        if (!empty($action)) {
            $className .= '@' . $action;
        }
        ThinkQueue::push($className, $data, $queueName);
    }

    /**
     * 延迟加入消息队列
     * @param int $secs
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatchLater(int $secs, array $data = [], ?string $action = '', ?string $queueName = null)
    {
        $className = __CLASS__;
        if (!empty($action)) {
            $className .= '@' . $action;
        }
        ThinkQueue::later($secs, $className, $data, $queueName);
    }
}
