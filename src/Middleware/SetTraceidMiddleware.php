<?php
declare(strict_types=1);

namespace Yuxk\Helper\Middleware;

use Yuxk\Helper\Trace;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SetTraceIdMiddleware implements MiddlewareInterface
{
    use Trace;
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $traceId = $request->getHeaderLine('trace_id');
        $this->putTraceId(null, $traceId);

        return $handler->handle($request);
    }
}