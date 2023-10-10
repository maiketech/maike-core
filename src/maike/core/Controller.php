<?php

namespace maike\core;

use think\App;
use think\Validate;
use think\exception\ValidateException;
use maike\utils\Json;

/**
 * 控制器基础类
 */
abstract class Controller
{
    /**
     * Request实例
     * @var \app\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->initialize();
    }

    /**
     * @return mixed
     */
    abstract protected function initialize();

    protected function params($key = '', $default = '')
    {
        return empty($key) ? $this->request->post() : $this->request->post($key, $default);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        $v->message($message);
        // 是否批量验证
        if ($batch) {
            $v->batch(true);
        }
        return $v->failException(true)->check($data);
    }

    /**
     * 返回成功json
     * @param array $data
     * @param string|array $msg
     * @return maike\utils\Json
     */
    protected function success($data = null, $msg = 'success')
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        return Json::Success($data, $msg);
    }

    /**
     * 返回失败json
     * @param string $msg
     * @param array $data
     * @return maike\utils\Json
     */
    protected function error($msg = 'error', $data = null)
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        return Json::Error($msg, $data);
    }
}
