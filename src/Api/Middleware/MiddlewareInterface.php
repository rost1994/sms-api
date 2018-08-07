<?php
declare(strict_types=1);

namespace Api\Sms\Api\Middleware;

use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

interface MiddlewareInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next);
}
