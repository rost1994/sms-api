<?php
declare(strict_types=1);

namespace Api\Sms;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Factory\ResponseFactory;
use Api\Sms\Api\Http\Response;

class ErrorHandler
{
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * ErrorHandler constructor.
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param ApplicationException $e
     *
     * @return Response
     */
    public function getResponseFromError(ApplicationException $e): Response
    {
        $error = [
            'errorMessage' => $e->getMessage() ?? 'Something went wrong',
        ];

        return $this->responseFactory->createJsonResponse(
            $e->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR,
            json_encode($error)
        );
    }
}
