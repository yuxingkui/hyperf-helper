<?php
declare(strict_types=1);

namespace Yuxk\Helper\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Context\Context;
use Hyperf\Utils\Coroutine;
use Yuxk\Helper\Trace;
use Hyperf\Di\Annotation\Aspect;

#[
    Aspect(
        classes: [
            "Yuxk\Helper\Factory\LoggerFactory::get"
        ]
    )
]

class LogAspect extends AbstractAspect
{
	use Trace;

	public function process(ProceedingJoinPoint $proceedingJoinPoint)
	{
        try {
            $parentId = \Hyperf\Utils\Coroutine::parentId();
        } catch (\RuntimeException $e) {

        }
		($parentId == -1) && $parentId = null;

		$this->putTraceId($parentId);
		$result = $proceedingJoinPoint->process();

		return $result;
	}
}