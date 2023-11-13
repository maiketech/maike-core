<?php

namespace maike\core;

use think\App;
use think\Validate;
use think\exception\ValidateException;
use think\facade\Config;
use think\Response;

/**
 * 控制器基础类
 */
abstract class Controller
{
    /**
     * Request实例
     * @var \maike\core\Request
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
     * @return \think\response\Json
     */
    protected function success($data = null, $msg = 'success', $code = 10000, $statusCode = 200, $header = [], $options = [])
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        if (!$code) {
            $code = Config::get("core.status_code.success");
        }
        $result = compact('code', 'msg', 'data');
        return Response::create($result, 'json', $statusCode)->header($header)->options($options);
    }

    /**
     * 返回失败json
     * @param string $msg
     * @param array $data
     * @return \think\response\Json
     */
    protected function error($msg = 'error', $data = null, $code = 0, $statusCode = 200, $header = [], $options = [])
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        $result = compact('code', 'msg', 'data');
        return Response::create($result, 'json', $statusCode)->header($header)->options($options);
    }
}
