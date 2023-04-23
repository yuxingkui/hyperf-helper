<?php
declare(strict_types=1);

namespace Yuxk\Helper;

use Hyperf\Logger\Logger;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Utils\Coroutine;
use Yuxk\Helper\Factory\LoggerFactory;

/**
 * @method static Logger get($name)
 * @method static void log($level, $message, array $context = [])
 * @method static void emergency($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void debug($message, array $context = [])
 */
class Log
{
    public static function __callStatic($name, $arguments)
    {
        $factory = di(LoggerFactory::class);
        if ($name === 'get') {
            return $factory->get(...$arguments);
        }
        $log = $factory->get('default');

        $log->$name(...$arguments);
    }
}