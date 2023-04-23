<?php
declare(strict_types=1);


namespace Yuxk\Helper;

use Hyperf\Context\Context;
use Hyperf\Utils\Coroutine;

/**
 * 设置trace_id
 *
 * Class SetTraceid
 * @package App\Job
 */
trait Trace
{

    /**
     * 设置traceId
     *
     * @param null $coroutineId
     * @param bool $traceId
     * @param bool $coverContext
     */
    protected function putTraceId($coroutineId = null, $traceId = false, $coverContext = false)
    {
        if ($coverContext || !Context::get('trace_id')) {
	        if (false === $traceId) {
		        $traceId = $this->getTraceId($coroutineId);
	        }

            $traceId || $traceId = $this->buildTraceId();
            Context::set('trace_id', $traceId);
        }
    }

    /**
     * 销毁TraceId
     */
    protected function clearTraceId()
    {
        Context::destroy('trace_id');
    }

    /**
     * 获取traceId
     *
     * @param int $coroutineId
     *
     * @return mixed|null
     */
    protected function getTraceId($coroutineId = null)
    {
        return Context::get('trace_id', null, $coroutineId);
    }

    /**
     * 生成TraceId
     *
     * @return string
     */
    private function buildTraceId()
    {
        return sha1(uniqid('',
                true) . str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)));
    }

}