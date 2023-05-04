<?php

declare(strict_types=1);

namespace Yuxk\Helper\Constants;

use Hyperf\Constants\Annotation\Constants;

#[Constants]
class SystemCode
{
    /**
     * @Message("redis过期时间！1天")
     */
    public const REDIS_TIMEOUT_DAY = 24 * 3600;

    /**
     * @Message("redis过期时间！")
     */
    public const REDIS_TIMEOUT = 24 * 3600 * 2;
    public const REDIS_SCH_TIMEOUT = 3600 * 2;

    // 1分钟
    public const REDIS_TIMEOUT_ONE_MINUTES = 60;

    //5分钟
    public const REDIS_TIMEOUT_FIVE_MINUTES = 300;

    // 10分钟
    public const REDIS_TIMEOUT_TEN_MINUTES = 600;

    /**
     * @Message("redis过期时间！")
     */
    public const REDIS_TIMEOUT_HOUR = 3600;


    /**
     * @Message("钉钉access_token redis ")
     */
    public const REDIS_DING_ACCESS_TOKEN = 'ding_access_token';
}
