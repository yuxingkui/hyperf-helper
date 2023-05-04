<?php

declare(strict_types=1);

namespace Yuxk\Helper\Services;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Guzzle\HandlerStackFactory;
use Yuxk\Helper\Log;

/**
 * http请求
 * Class RequestServices
 * @method array get(string $uri, array $params = [])
 * @method array head(string $uri, array $params = [])
 * @method array put(string $uri, array $params = [])
 * @method array patch(string $uri, array $params = [])
 * @method array delete(string $uri, array $params = [])
 * @method array getAsync(string $uri, array $params = [])
 * @method array headAsync(string $uri, array $params = [])
 * @method array putAsync(string $uri, array $params = [])
 * @method array postAsync(string $uri, array $params = [])
 * @method array patchAsync(string $uri, array $params = [])
 * @method array deleteAsync(string $uri, array $params = [])
 */
class RequestServices
{
    // clientFactory
    private $clientFactory;

    // host
    private $host;

    // path
    private $path;

    // url
    private $url;

    //header
    private $header = [];

    //body
    private $body = '';

    // formatResult
    private $formatResult = 'array';

    const MAX_CONNECTIONS = 100;

    public function __construct()
    {
        $this->clientFactory = $this->getClient();
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        $factory = new HandlerStackFactory();
        $stack = $factory->create([
            'max_connections' => self::MAX_CONNECTIONS
        ]);

        return make(Client::class, [
            'config' => [
                'handler' => $stack,
            ],
        ]);
    }

    /**
     * @param $formatResult
     */
    public function setFormatResult($formatResult)
    {
        $this->formatResult = $formatResult;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormatResult(): string
    {
        return $this->formatResult;
    }

    public function __call($method, $params)
    {
        try {
            $url = $params[0];
            $options = $params[1] ?? [];

            //guzzle handler
            $response = $this->clientFactory->{$method}($url, $options);

            if ($this->getFormatResult() == 'array') {
                $data = $response->getBody()->getContents();
                return $data ? json_decode($data, true) : [];
            }
            return $response;

        } catch (\Throwable $e) {
            Log::error($method, [
                'param' => $params,
                'throwable' => $e,
            ]);

            return [
                'code' => 500,
                'msg' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
