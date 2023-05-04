<?php

declare(strict_types=1);

namespace Yuxk\Helper\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("参数错误")
     */
    public const PARAMETER_ERROR = 100000;

    /**
     * @Message("操作失败")
     */
    public const OPERATION_FAILED = 100001;

    /**
     * @Message("数据库错误失败")
     */
    public const OPERATION_FAILED_TO_SQL = 100002;

    /**
     * @Message("频率限制")
     */
    public const HTTP_API_IP_FREQUENTLY = 100201;

    /**
     * @Message("HTTP服务响应错误1")
     */
    public const HTTP_API_RESPONSE_ERROR1 = 100202;

    /**
     * @Message("HTTP服务响应错误2")
     */
    public const HTTP_API_RESPONSE_ERROR2 = 100203;

    /**
     * @Message("远程HTTP服务错误")
     */
    public const HTTP_API_SERVICE_ERROR = 100204;


}
