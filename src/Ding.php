<?php

namespace Yuxk\Helper;

use Yuxk\Helper\Constants\ErrorCode;
use Yuxk\Helper\Constants\SystemCode;
use Yuxk\Helper\Log;
use Yuxk\Helper\Redis;
use http\Exception\RuntimeException;
use Yuxk\Helper\Services\RequestServices;

/**
 * http请求
 * Class Ding
 * @method array postDeptChildList(array $body = [])
 * @method array postCreateApproval(array $body = [])
 * @method array postUserInfoByMobile(array $body = [])
 * @method array postProcessInstanceGet(array $body = [])
 */
class Ding
{
    private static $_instance;

    //appKey
    public static string $appKey;
    public static string $appSecret;
    public static string $agentId;

    //服务器地址
    public static string $serverHost;
    //审批实例
    private static $processCode;

    private RequestServices $client;

    public array $config;

    public static string $token_url = '/gettoken';//获取token


    public const POST_DEPT_CHILD_LIST = '/topapi/v2/department/listsubid'; //获取子部门ID列表
    public const POST_CREATE_APPROVAL = '/topapi/processinstance/create';//发起审批实例
    public const POST_USER_INFO_BY_MOBILE = '/topapi/v2/user/getbymobile'; //根据手机号获取用户信息
    public const POST_PROCESS_INSTANCE_GET = '/topapi/processinstance/get';//获取单个审批实例详情

    public function __construct()
    {
        $this->config = config('ding');
        self::$appKey = $this->config['app_key'];
        self::$appSecret = $this->config['app_secret'];
        self::$serverHost = $this->config['server_host'];
        self::$agentId = $this->config['agent_id'];
        self::$processCode = $this->config['process_code'];

        $this->client = make(RequestServices::class);


    }


    public static function getInstance($options = null): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static($options);
        }

        return self::$_instance;
    }


    //获取钉钉token
    public function getDingToken(): string
    {
        $accessToken = Redis::get(SystemCode::REDIS_DING_ACCESS_TOKEN);

        if (empty($accessToken)) {
            //请求地址
            $url = self::$serverHost.self::$token_url.'?appkey='.self::$appKey.'&appsecret='.self::$appSecret;

//            $response = $this->client->request('GET', $url);
            $response = $this->client->get($url);

            if (empty($response) || $response['errcode'] != 0) {
                Log::error('钉钉接口错误', [$response]);

                throw new RuntimeException('钉钉接口错误', ErrorCode::HTTP_API_SERVICE_ERROR);
            }

            $accessToken = $response['access_token'];

            Redis::setex(SystemCode::REDIS_DING_ACCESS_TOKEN, SystemCode::REDIS_TIMEOUT_HOUR, $accessToken);
        }

        return $accessToken;
    }


    public function __call(string $name, array $arguments = [])
    {
        $requestParam = $arguments[0] ?? [];

        try {

            $method = explode('_', unCamelize($name))[0];

            $url = self::$serverHost.constant('self::'.strtoupper(unCamelize($name)));

            $url .= '?access_token='.self::getDingToken();

            $response = $this->client->{$method}($url, $requestParam);

        } catch (\Exception $e) {
            if (empty($response) || $response['errcode'] != 0) {
                Log::error('钉钉接口错误', [$response]);
                throw new \RuntimeException(
                    '钉钉接口错误',
                    ErrorCode::HTTP_API_SERVICE_ERROR
                );
            }
        }

        return $response;
    }

}
