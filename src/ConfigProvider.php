<?php
declare(strict_types=1);


namespace Yuxk\Helper;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\IdGeneratorInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Request;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Yuxk\Helper\Aspect\LockAspect;
use Yuxk\Helper\Aspect\LogAspect;
use Yuxk\Helper\Contracts\NotifyInterface;
use Yuxk\Helper\Notify\DingTalk;

/**
 * Class ConfigProvider
 * @package Yuxk\Helper
 */
class ConfigProvider
{
	public function __invoke(): array
	{
		return [
			'dependencies' => [
				NotifyInterface::class      => DingTalk::class,
				RequestInterface::class     => Request::class,
				IdGeneratorInterface::class => SnowflakeIdGenerator::class,
			],
			'middlewares'  => [
				'http' => [
					\Yuxk\Helper\Middleware\SetTraceIdMiddleware::class,
				],
			],
			'aspects'      => [
				LogAspect::class,
				LockAspect::class,
			],
			'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
			'hosts'        => [
				"dingding" => [
					'host' => "https://oapi.dingtalk.com/",
					'path' => [
						'robotSend'  => 'robot/send',
					],
				],
			],
			'publish'      => [
				[
					'id'          => 'dingTaklConfig',
					'description' => 'dingtalk 配置文件',
					'source'      => __DIR__ . '/../publish/dingtalk.php',
					'destination' => BASE_PATH . '/config/autoload/dingtalk.php',
				],
			],
		];
	}
}