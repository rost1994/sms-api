<?php
declare(strict_types=1);

namespace Api\Sms\Api\Http\Factory;

use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

class ResponseFactory
{
    /**
     * @param int $statusCode
     * @param string $content
     *
     * @return Response
     */
    public function createJsonResponse(int $statusCode = 200, string $content = ''): Response
    {
        return new Response($statusCode, $content, ['Content-Type' => Request::CONTENT_JSON]);
    }
}