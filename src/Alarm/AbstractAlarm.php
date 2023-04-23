<?php
declare(strict_types=1);

namespace Yuxk\Helper\Alarm;

use Carbon\Carbon;

abstract class AbstractAlarm
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var array
	 */
	protected $parameter = [];

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $env;

	/**
	 * @var string
	 */
	protected $time;

	public function __construct(string $message, string $title = '', array $parameter = [])
	{
		$this->message = $message;
		$this->title   = $this->setTitle($title);
		$this->env     = '本机';
		$this->time    = Carbon::now()->toDateTimeString();

		$this->setPath();
		$this->setParameter($parameter);
	}

	abstract protected function setPath();

	abstract protected function setParameter(array $parameter);

	protected function setTitle($title)
	{
		if (! empty($title)) {
			return $title;
		}

		return $this->getDefaultTitle();
	}

	protected function getDefaultTitle()
	{
		return '报警信息';
	}

	public function __toString()
	{
		return implode(
				PHP_EOL,
				[
					'标题:' . $this->title,
					'环境:' . $this->env,
					'地址:' . $this->path,
					'错误:' . $this->message,
					'参数:' . json_encode($this->parameter),
					'时间:' . $this->time,
				]
			) . PHP_EOL;
	}
}