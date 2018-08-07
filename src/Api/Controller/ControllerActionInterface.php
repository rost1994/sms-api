<?php
declare(strict_types=1);

namespace Api\Sms\Api\Controller;

use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

interface ControllerActionInterface
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function execute(Request $request): Response;
}