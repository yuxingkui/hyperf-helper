<?php
declare(strict_types=1);


namespace Yuxk\Helper;

use Hyperf\Utils\ApplicationContext;

/**
 * Class Redis
 * @package Yuxk\Helper
 * @method \Redis get(string $key)
 * @method \Redis set($key, $value, $timeout)
 * @method \Redis setex($key, $ttl, $value)
 * @method \Redis setnx($key, $value)
 */
class Redis
{

    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();

        $redis = $container->get(\Hyperf\Redis\Redis::class);

        return $redis->{$name}(...$arguments);
    }

}
