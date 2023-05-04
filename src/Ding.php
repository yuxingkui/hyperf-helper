<?php

namespace Yuxk\Helper;

use Yuxk\Helper\Constants\ErrorCode;
use Yuxk\Helper\Constants\SystemCode;
use Yuxk\Helper\Log;
use Yuxk\Helper\Redis;
use http\Exception\RuntimeException;
use Yuxk\Helper\Services\RequestServices;

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
    public static string $createProcessInstance = '/topapi/processinstance/create';//发起审批实例
    public static string $getProcessInstance = '/topapi/processinstance/get';//获取单个审批实例详情

    public static string $getUserInfoByMobile = '/topapi/v2/user/getbymobile'; //根据手机号获取用户信息

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

    public function getUserId(string $mobile): string
    {
        //请求地址
        $url = self::$serverHost.self::$getUserInfoByMobile.'?access_token='.self::getDingToken();

        $requestParam['json'] =  ['mobile' => $mobile];

        $response = $this->client->request('POST', $url, $requestParam);


        if (empty($response) || $response['errcode'] != 0) {
            Log::error('钉钉接口错误', [$response]);
            throw new \RuntimeException('钉钉接口错误', ErrorCode::HTTP_API_SERVICE_ERROR);
        }

        return $response['result']['userid'];
    }


    //发起钉钉审批流程
    public  function createApproval(array $content) :string
    {
        //请求地址
        $url = self::$serverHost.self::$createProcessInstance.'?access_token='.self::getDingToken();

        $requestParam['json'] =  $content;

        $response = $this->client->request('POST', $url, $requestParam);

        if (empty($response) || $response['errcode'] != 0) {
            Log::error('钉钉接口错误', [$response]);
            throw new \RuntimeException('钉钉接口错误', ErrorCode::HTTP_API_SERVICE_ERROR);
        }

        return $response['process_instance_id']; //审批实例
    }

    public function getDetails(string $processInstanceId): array
    {
        $url = self::$serverHost . self::$getProcessInstance . '?access_token='
            . self::getDingToken();
        $requestParam['json'] = ['process_instance_id' => $processInstanceId];

        $response = $this->client->request('POST', $url, $requestParam);

        if (empty($response) || $response['errcode'] != 0) {
            Log::error('钉钉接口错误', [$response]);
            throw new RuntimeException(
                '钉钉接口错误',
                ErrorCode::HTTP_API_SERVICE_ERROR
            );
        }

        return $response['process_instance'];
    }


    public function __call(string $name, array $arguments = [])
    {
        $requestParam = $arguments[0] ?? [];

        try {

            $url = self::$serverHost.constant('self::'.strtoupper(unCamelize($name)));

            $url = $url.'?access_token='.self::getDingToken();

            $response = $this->client->request('POST', $url, $requestParam);

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
