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
 * @method array get(string $uri, array $params, string $type = "")
 * @method array head(string $uri, array $params, string $type = "")
 * @method array put(string $uri, array $params, string $type = "")
 * @method array patch(string $uri, array $params, string $type = "")
 * @method array delete(string $uri, array $params, string $type = "")
 * @method array getAsync(string $uri, array $params, string $type = "")
 * @method array headAsync(string $uri, array $params, string $type = "")
 * @method array putAsync(string $uri, array $params, string $type = "")
 * @method array postAsync(string $uri, array $params, string $type = "")
 * @method array patchAsync(string $uri, array $params, string $type = "")
 * @method array deleteAsync(string $uri, array $params, string $type = "")
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
     * @return $this
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

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param  array $header
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param  string $body
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param $urlConfigStr
     */
    private function setUrl($urlConfigStr)
    {
        $uriSplit = $this->uriCheck($urlConfigStr);
        if ($uriSplit) {
            $this->url = $urlConfigStr;

            return;
        }

        [$hostStr, $pathStr] = explode('.', $urlConfigStr);

        $this->host = config('hosts.' . $hostStr . '.host');
        $this->path = config('hosts.' . $hostStr . '.path.' . $pathStr);

        $this->url = rtrim($this->host, '/') . '/' . $this->path;
    }

    /**
     * check uri is http/https
     *
     * @param string $urlConfigStr
     *
     * @return false|int
     */
    private function uriCheck($urlConfigStr)
    {
        $preg = '/^http(s)?:\\/\\/.+/';

        return preg_match($preg, $urlConfigStr);
    }

    public function __call($method, $params)
    {
        try {
        	$types = [
                'post' => 'form_params',
                'get' => 'query',
            ];
            $m = strtolower($method);

            $defaultParams = [
                '',
                [],
                $types[$m] ?? 'query',
            ];

            //set default
            $paramsWithDefault = $params + $defaultParams;
            //assign value
            [$url, $requestParams, $type] = $paramsWithDefault;
            //parse url
            $this->setUrl($url);
            $url = $this->url;
            //构造guzzle options
            $options = [];
            if ($headers = $this->getHeader()) {
                $options = array_merge($options, ['headers' => $headers]);
            }

            if ($body = $this->getBody()) {
                $options = array_merge($options, ['body' => $body]);
            }

            if (! empty($requestParams)) {
                $options = array_merge($options, [$type => $requestParams]);
            }

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
