<?php
declare (strict_types=1);

use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;

if (false === function_exists('di')) {
	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param null|mixed $id
	 *
	 * @return mixed|\Psr\Container\ContainerInterface
	 */
	function di($id = null)
	{
		$container = ApplicationContext::getContainer();
		if ($id) {
			return $container->get($id);
		}

		return $container;
	}
}


if (false === function_exists('format_throwable')) {
	/**
	 * Format a throwable to string.
	 *
	 * @param Throwable $throwable
	 *
	 * @return string
	 */
	function format_throwable(Throwable $throwable): string
	{
		return di()
			->get(FormatterInterface::class)
			->format($throwable);
	}
}


if (false === function_exists('yuan2fen')) {
	/**
	 * 人民币元转为人民币分
	 *
	 * @param float $yuan
	 *
	 * @return integer
	 */
	function yuan2fen($yuan): int
	{
		return (int)bcmul((string)$yuan, '100');
	}
}


if (false === function_exists('fen2yuan')) {
	/**
	 * 人民币分转为人民币元
	 *
	 * @param integer $fen
	 *
	 * @return float
	 */
	function fen2yuan(int $fen)
	{
		return bcdiv((string)$fen, '100', 2);
	}
}

if (false === function_exists('avgAmount')) {

	/**
	 * 根据数组值占比，分摊总数尾差挂数组最后一元素
	 *
	 * $data = [ 123 => 500, 124 => 800]
	 * @param int $total
	 * @param array $data
	 *
	 * @return array
	 */
	function avgAmount(int $total, array $data): array
	{
		$avg = [];
		asort($data);

		$ratioList = avgRatio($data);
		$diff      = $total;
		foreach ($data as $key => $item) {
			$avg[$key] = bcmul((string)$total, (string)$ratioList[$key]);
			$diff      = bcsub((string)$diff, $avg[$key]);
		}

		$keys = array_keys($data);
		$key  = end($keys);

		$avg[$key] += $diff;

		return $avg;
	}
}

if (false === function_exists('avgRatio')) {
	/**
	 * 计算平均占比
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	function avgRatio(array $data): array
	{
		$total = array_sum($data);

		$return = [];
		foreach ($data as $key => $item) {
			$ratio = bcdiv((string)$item, (string)$total, 4);

			$return[$key] = $ratio;
		}

		return $return;
	}
}

if (function_exists('camelize') === false) {
    /**
     * 下划线转驼峰
     * @param string $words
     * @param string $separator
     * @return string
     */
    function camelize(string $words, string $separator = '_'): string
    {
        $words = $separator.str_replace($separator, " ", strtolower($words));

        return ltrim(str_replace(" ", "", ucwords($words)), $separator);
    }
}

if (false === function_exists('unCamelize')) {
    /**
     * 驼峰转下划线
     * @param string $camelCaps
     * @param string $separator
     * @return string
     */
    function unCamelize(string $camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1".$separator."$2", $camelCaps));
    }
}

if (false === function_exists('arraySplit')) {

	/**
	 * 根据条件拆分二维数组
	 *
	 * $data = [ ['column'=> 1, 'c1' => 1] , ['column'=> 2, 'c1' => 66], ['column'=> 2, 'c1' => 77],  ... ]
	 * return [
	 *      1 => [ ['column'=> 1, 'c1' => 1] , ]
	 *      2 => [ ['column'=> 2, 'c1' => 66], ['column'=> 2, 'c1' => 77],  ]
	 * ]
	 *
	 * @param array $data
	 * @param string $column
	 * @param mixed $value
	 *
	 * @return array
	 */
	function arraySplit(array $data, string $column, $value): array
	{
		$surplus = [];
		$result  = array_filter($data,
			function ($v, $k) use (&$surplus, $column, $value) {
				if ($value != $v[$column]) {
					$surplus[] = $v;

					return false;
				}

				return true;
			}, ARRAY_FILTER_USE_BOTH);

		return [$result, $surplus];
	}
}

if (false === function_exists('arrayGroup')) {

	/**
	 * 根据数字内字段值分多个数组
	 *
	 * @param array $data
	 * @param string $keyColumn
	 * @param string $valColumn
	 *
	 * @return array
	 */
	function arrayGroup(array $data, string $keyColumn, string $valColumn = ''): array
	{
		if (!$data) {
			return [];
		}
		$result = [];
		foreach ($data as $key => $item) {
			$result[$item[$keyColumn]][] = $valColumn ? $item[$valColumn] : $item;
		}

		return $result;
	}
}

if (false === function_exists('runningInHttpServer')) {
	function runningInHttpServer()
	{
		$serverName = null;
		try {
			$serverName = di(Hyperf\HttpServer\Server::class)->getServerName();
		} catch (Throwable $e) {
		}

		return isset($serverName) ? true : false;
	}
}

if (false === function_exists('alarm')) {
	function alarm($message = '', $title = '', $parameter = [])
	{
		/*
		 * 目前1.1 闭包环境下执行会报如下问题:
		 * @see https://zhuanlan.zhihu.com/p/143659819
		 * 2.0 已经解决，先粗暴解决一下
		 */
		try {
			if (runningInHttpServer()) {
				$alarmObj = new \Yuxk\Helper\Alarm\HttpServerAlarm($message, $title, $parameter);
			} else {
				$alarmObj = new \Yuxk\Helper\Alarm\ConsoleAlarm($message, $title, $parameter);
			}
		} catch (Throwable $e) {
			$alarmObj = $message;
		}

		//多重报警可以改写成事件监听
		return di(\Yuxk\Helper\Contracts\NotifyInterface::class)->sendMsg((string)$alarmObj);
	}
}

if (!function_exists('apiSucc')) {
	/**
	 * 构建成功的接口返回数据
	 *
	 * @param $data
	 *
	 * @return array
	 */
	function apiSucc($data = [])
	{
		return [
			'code' => 0,
			'msg'  => 'ok',
			'data' => $data,
		];
	}
}

if (!function_exists('apiErr')) {
	/**
	 * 构建成功的接口返回数据
	 *
	 * @param int $code
	 * @param string $message
	 *
	 * @return array
	 */
	function apiErr($code, $message)
	{
		return [
			'code' => $code,
			'msg'  => $message,
			'data' => [],
		];
	}
}

if (!function_exists('getPkId')) {
	/**
	 * 获取主键id
	 *
	 * @return int
	 */
	function getPkId()
	{
		$container = ApplicationContext::getContainer();
		$generator = $container->get(\Hyperf\Snowflake\IdGeneratorInterface::class);

		return $generator->generate();
	}
}

if (!function_exists('runWithLock')) {
	/**
	 * 加锁执行一个方法
	 * @param Closure $callback
	 * @param int $lockValue
	 * @param int $lockSec
	 * @param string $lockName
	 */
	function runWithLock(Closure $callback, $lockName = '', $lockValue = null, $lockSec = 60)
	{
		// 防止他人删锁
		if (! isset($lockValue)) {
			$lockValue = getPkId();
		}
        $lock = make(\Yuxk\Helper\Lib\RedisLock::class, ['name' => $lockName, 'seconds' => $lockSec, 'owner' => $lockValue]);
        $result = $lock->get($callback);
        return $result;
	}
}