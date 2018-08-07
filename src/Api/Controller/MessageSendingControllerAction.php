<?php
declare(strict_types=1);

namespace Api\Sms\Api\Controller;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Factory\ResponseFactory;
use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;
use Api\Sms\Sender\SmsHandler;
use MessageBird\Objects\Message;

class MessageSendingControllerAction implements ControllerActionInterface
{
    /**
     * @var SmsHandler
     */
    private $sender;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * MessageSendingControllerAction constructor.
     * @param SmsHandler $sender
     * @param ResponseFactory $responseFactory
     */
    public function __construct(SmsHandler $sender, ResponseFactory $responseFactory)
    {
        $this->sender = $sender;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ApplicationException
     */
    public function execute(Request $request): Response
    {
        $requestBody = $request->getBody();

        $this->validate($requestBody);

        foreach ($requestBody['recipients'] as &$recipient) {
            $recipient = filter_var((string)$recipient, FILTER_SANITIZE_NUMBER_INT);
        }
        unset($recipient);

        $message = new Message();
        $message->originator = (string)$requestBody['originator'];
        $message->recipients = $requestBody['recipients'];
        $message->body = (string)$requestBody['body'];

        $groupMessageId = $this->sender->handle($message);

        $response = [
            'groupMessageId' => $groupMessageId,
        ];

        return $this->responseFactory->createJsonResponse(Response::HTTP_OK, json_encode($response));
    }

    /**
     * @param array $requestBody
     *
     * @throws ApplicationException
     */
    private function validate(array $requestBody): void
    {
        $requiredFields = ['originator', 'recipients', 'body'];

        foreach ($requiredFields as $requiredField) {
            if (empty($requestBody[$requiredField])) {
                throw new ApplicationException(
                    sprintf(
                        'Some required fields are missing. Required fields are: %s',
                        implode(', ', $requiredFields)
                    ),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        if (!is_array($requestBody['recipients']) || empty($requestBody['recipients'])) {
            throw new ApplicationException(
                'Recipients should be not empty array',
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
