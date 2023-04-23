<?php

namespace Yuxk\Helper\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RunWithLock extends AbstractAnnotation
{
	/**
	 * @var string
	 */
	public $lockName;

	/**
	 * @var integer
	 */
	public $lockValue;

	/**
	 * @var integer
	 */
	public $lockSec;

}