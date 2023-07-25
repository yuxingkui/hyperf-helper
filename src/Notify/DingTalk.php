<?php

declare(strict_types=1);

namespace Yuxk\Helper\Notify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Yuxk\Helper\Contracts\NotifyInterface;
use Yuxk\Helper\Ding;
use Yuxk\Helper\Log;
use Yuxk\Helper\Services\RequestServices;

class DingTalk implements NotifyInterface
{
    const MESSAGE_TYPE_TEXT = 'text';
    const MESSAGE_TYPE_LINK = 'link';
    const MESSAGE_TYPE_IMAGE = 'image';
    const MESSAGE_TYPE_VOICE = 'voice';
    const MESSAGE_TYPE_FILE = 'file';
    const MESSAGE_TYPE_OA = 'oa';
    const MESSAGE_TYPE_MARKDOWN = 'markdown';
    const MESSAGE_TYPE_ACTION_CARD = 'action_card';

    const TIME_OUT = 2;

    /**
     * @var Client
     */
    protected $client;

    protected $config;

    protected $ding;
    public function __construct()
    {
        $this->client = make(RequestServices::class);

        $this->ding = make(Ding::class);

        $this->config = di(ConfigInterface::class);
    }

    public function sendMsg(string $msgType, array $message, string $to)
    {
        // 禁用
        if (! $this->config->get('ding.enable')) {
            return;
        }

        $accessToken = $this->ding->getInstance()->getDingToken();

        $url = config('ding.server_host') . config('ding.async_send') . '?access_token=' . $accessToken;

        $body['agent_id'] = config('ding.agent_id');
        $body['userid_list'] = $to;
        $body['msg'] = json_encode(
            array_merge(['msgtype' => $msgType], ["$msgType" => $message]),
            JSON_UNESCAPED_UNICODE
        );

        $promise = $this->client->setFormatResult('none')->postAsync($url, ['form_params' => $body]);

        $promise->then(
            function (ResponseInterface $res) {
                Log::info('Send DingDing Talk[http_code:' . $res->getStatusCode() . '|message:' . $res->getBody()->getContents() . ']',$body);
            },
            function (RequestException $e) {
                Log::error($e->getMessage());
            }
        );
        $promise->wait();
        return true;
    }
}