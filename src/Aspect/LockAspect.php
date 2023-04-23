<?php

namespace Yuxk\Helper\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Yuxk\Helper\Annotation\RunWithLock;
use Hyperf\Di\Annotation\Aspect;

#[
    Aspect(
        classes: [
            "Yuxk\Helper\Annotation\RunWithLock::class"
        ]
    )
]
class LockAspect extends AbstractAspect
{

	public function process(ProceedingJoinPoint $proceedingJoinPoint)
	{
		$annotation = $this->getAnnotations($proceedingJoinPoint);
		$lockName = $annotation->lockName ?? '';
		$lockValue = $annotation->lockValue;
		$lockSec = $annotation->lockSec;
		
		if (! empty($lockName)) {
			return \runWithLock(function() use ($proceedingJoinPoint) {
				return $proceedingJoinPoint->process();
			}, $lockName, $lockValue, $lockSec);
		}
		
		$result = $proceedingJoinPoint->process();
		return $result;
	}
	
	public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): ?RunWithLock
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return $metadata->method[RunWithLock::class] ?? null;
    }
}