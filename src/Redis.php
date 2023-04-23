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
		$redis     = $container->get(\Redis::class);

		if (is_string($arguments[0])) {
			$arguments[0] = config('redis.prefix', '') . $arguments[0];
		} elseif (is_array($arguments[0])) {
			foreach ($arguments[0] as &$key) {
				$key = config('redis.prefix', '') . $key;
			}
		}

		return $redis->$name(...$arguments);
	}

	public function __call($name, $arguments)
	{
		$container = ApplicationContext::getContainer();
		$redis     = $container->get(\Redis::class);

		if (! in_array($name, ['eval'])) {
			if (is_string($arguments[0])) {
				$arguments[0] = config('redis.prefix', '') . $arguments[0];
			} elseif (is_array($arguments[0])) {
				foreach ($arguments[0] as &$key) {
					$key = config('redis.prefix', '') . $key;
				}
			}
		}
		return $redis->$name(...$arguments);
	}

	/**
	 * @param string $key
	 * @param int $timeout
	 * @return string|null
	 */
	public static function pop(string $key, int $timeout)
	{
		$data = Redis::brPop($key, $timeout);

		return count($data) ? $data[1] : null;
	}

	/**
	 *
	 * @param string $key
	 * @param string|mixed $value
	 * @return mixed
	 */
	public static function push($key, ...$value)
	{
		return Redis::lpush($key, ...$value);
	}

}
