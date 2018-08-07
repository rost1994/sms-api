<?php
declare(strict_types=1);

namespace Api\Sms\Api\Controller;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Factory\ResponseFactory;
use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;
use Api\Sms\Sender\Entity\MessageContainer;
use Api\Sms\Sender\MongoDb\Repository\MessageRepository;

class MessageStatusControllerAction implements ControllerActionInterface
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * MessageStatusControllerAction constructor.
     * @param ResponseFactory $responseFactory
     * @param MessageRepository $messageRepository
     */
    public function __construct(ResponseFactory $responseFactory, MessageRepository $messageRepository)
    {
        $this->responseFactory = $responseFactory;
        $this->messageRepository = $messageRepository;
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
        $groupId = $request->getAttributes()['id'] ?? '';

        $messageContainers = $this->messageRepository->findMessagesByGroupId($groupId);
        if (empty($messageContainers)) {
            throw new ApplicationException('Incorrect message id', Response::HTTP_BAD_REQUEST);
        }

        // Priorities of statuses.
        $statusDictionary = [
            MessageContainer::STATUS_FINISHED => 0,
            MessageContainer::STATUS_WAITING => 1,
            MessageContainer::STATUS_PENDING => 2,
            MessageContainer::STATUS_FAILED => 3,
        ];

        $status = array_reduce(
            $messageContainers,
            function ($status, MessageContainer $messageContainer) use ($statusDictionary) {
                $currentMessageStatusPriority = $statusDictionary[$messageContainer->status];
                $groupMessageStatusPriority = $statusDictionary[$status];

                if ($currentMessageStatusPriority > $groupMessageStatusPriority) {
                    return $messageContainer->status;
                }

                return $status;
            },
            MessageContainer::STATUS_FINISHED
        );

        $response = [
            'status' => $status
        ];

        return $this->responseFactory->createJsonResponse(Response::HTTP_OK, json_encode($response));
    }
}
