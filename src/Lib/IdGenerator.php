<?php
declare(strict_types=1);

namespace Yuxk\Helper\Lib;

use Hyperf\Snowflake\IdGeneratorInterface;

trait IdGenerator
{
	/**
	 * 生成主键Id
	 */
	public static function makePrimaryKeyId()
	{
		return di(IdGeneratorInterface::class)->generate();
	}
}