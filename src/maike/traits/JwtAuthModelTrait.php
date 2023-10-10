<?php

namespace maike\traits;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Env;
use think\facade\Request;
use UnexpectedValueException;
use maike\exception\ApiException;

trait JwtAuthModelTrait
{
    //token过期时间
    protected $tokenExpire = 0;

    /**
     * 创建Token
     * 
     * @param string $type
     * @param array $params
     * @return array
     */
    public function createToken(array $params = []): array
    {
        $id = $this->{$this->getPk()};
        $host = Request::host();
        $time = time();
        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $this->tokenExpire > 0 ? $this->tokenExpire : strtotime('+1 days'),
        ];
        $params['info'] = compact('id');
        $token = JWT::encode($params, Env::get('APP_KEY'), 'HS256');
        return compact('token', 'params');
    }

    /**
     * 通过Token获取模型数据
     * 
     * @param string $jwt
     * @return mixed
     */
    public static function parseToken(string $jwt): mixed
    {
        try {
            JWT::$leeway = 60;
            $data = JWT::decode($jwt, new Key(Env::get('APP_KEY'), 'HS256'));
            if ($data && $data->info->id) {
                return self::get($data->info->id);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
