<?php

namespace maike\middlewares;

use Closure;
use think\facade\Config;
use think\Request;
use think\Response;
use maike\interfaces\MiddlewareInterface;

/**
 * 跨域请求支持
 * @package cores\middleware
 */
class AllowCrossDomain implements MiddlewareInterface
{
    /**
     * 允许跨域的域名
     * @var string
     */
    protected $cookieDomain;

    /**
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->cookieDomain = Config::get('cookie.domain', '');
        $header = Config::get('api.header');
        $origin = $request->header('origin');

        if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain))) {
            $header['Access-Control-Allow-Origin'] = $origin;
        }

        if ($request->method(true) == 'OPTIONS') {
            $response = Response::create('ok')->code(200)->header($header);
        } else {
            $response = $next($request)->header($header);
        }
        return $response;
    }
}
