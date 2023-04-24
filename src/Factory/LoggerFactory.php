<?php
declare(strict_types=1);


namespace Yuxk\Helper\Factory;

use Hyperf\Logger\Logger;
use Hyperf\Logger\LoggerFactory as HyperfLoggerFactory;
use Hyperf\Context\Context;
use Psr\Log\LoggerInterface;

/**
 * 日志工具
 *
 * Class LoggerFactory
 * @package App\Factory
 */
class LoggerFactory extends HyperfLoggerFactory
{

    public function get($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        if (isset($this->loggers[$name]) && $this->loggers[$name] instanceof Logger) {
            return $this->loggers[$name];
        }

        $logger = $this->make($name, $group);
        $logger->pushProcessor(function ($record) {
            $record['extra']['host'] = gethostname();

            $record['extra']['trace_id'] = Context::getOrSet('trace_id', (string)getPkId());

            return $record;
        });

        return $this->loggers[$name] = $logger;
    }
}