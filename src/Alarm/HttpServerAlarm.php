<?php
declare(strict_types=1);

namespace Yuxk\Helper\Alarm;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

class HttpServerAlarm extends AbstractAlarm
{
	protected $request;

	protected function setPath()
	{
		$request = di(RequestInterface::class);
		$this->path = ($request->url() ?? '');
	}

	protected function setParameter(array $parameter)
	{
		// TODO: Implement setParameter() method.
		$request = di(RequestInterface::class);
		if (! empty($parameter)) {
			$this->parameter = $parameter;
		}else{
			$this->parameter = $request->all() ?? [];
		}
	}
}