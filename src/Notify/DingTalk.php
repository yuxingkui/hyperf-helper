<?php

declare(strict_types=1);

namespace Yuxk\Helper\Notify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Yuxk\Helper\Contracts\NotifyInterface;
use Yuxk\Helper\Log;
use Yuxk\Helper\Services\RequestServices;

class DingTalk implements NotifyInterface
{
    const MESSAGE_TYPE = 'text';

    const TIME_OUT = 2;

    /**
     * @var Client
     */
    protected $client;

    protected $config;

    public function __construct()
    {
        $this->client = make(RequestServices::class);

        $this->config = di(ConfigInterface::class);
    }

    public function sendMsg(string $message, $to = '')
    {
        // 禁用
        if (! $this->config->get('dingtalk.enable')) {
            return;
        }

        $ms = $this->getMsectime();
        $sign = $this->getSign($ms);

        $accessToken = $this->config->get('dingtalk.access_token');

        $to = config('hosts.dingding.host') . config('hosts.dingding.path.robotSend') . '?access_token=' . $accessToken . '&timestamp=' . $ms . '&sign=' . $sign;

        $body = json_encode([
            'msgtype' => self::MESSAGE_TYPE,
            'text' => [
                'content' => $message,
            ],
            'at' => [
                'atMobiles' => [],
                'isAtAll' => true,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $promise = $this->client
            ->setFormatResult('none')
            ->setHeader(['content-type' => 'application/json;charset=utf-8'])
            ->setBody($body)
            ->postAsync($to, []);

        $promise->then(
            function (ResponseInterface $res) {
                Log::info('Send DingDing Talk[http_code:' . $res->getStatusCode() . '|message:' . $res->getBody()->getContents() . ']');
            },
            function (RequestException $e) {
                Log::error($e->getMessage());
            }
        );
        $promise->wait();

        return true;
    }

    /* 获取密钥 */
    protected function getSign($timestamp)
    {
        $secret = $this->config->get('dingtalk.secret');
        //把timestamp+"\n"+密钥当做签名字符串，使用HmacSHA256算法计算签名，然后进行Base64 encode，最后再把签名参数再进行urlEncode，得到最终的签名
        return urlencode(base64_encode(hash_hmac('sha256', $timestamp . "\n" . $secret, $secret, true)));
    }

    protected function getMsectime()
    {
        $microtimeArr = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($microtimeArr[0]) + floatval($microtimeArr[1])) * 1000);
    }
}
