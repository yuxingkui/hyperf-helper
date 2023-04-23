<?php
declare(strict_types=1);

namespace Yuxk\Helper\Alarm;

class ConsoleAlarm extends AbstractAlarm
{
	protected function setPath()
	{
		$this->path = implode(' ', data_get($_SERVER, 'argv')) ?? '';
	}

	protected function setParameter(array $parameter)
	{
		// TODO: Implement setParameter() method.
		return $this->parameter = ($parameter ?? []);
	}


}