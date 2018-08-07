<?php declare(strict_types=1);

namespace Api\Sms\Sender;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Response;
use Api\Sms\Sender\Entity\MessageContainer;
use Api\Sms\Sender\MongoDb\Repository\MessageManager;
use Api\Sms\Sender\MongoDb\Repository\MessageRepository;
use MessageBird\Client;
use MessageBird\Exceptions\AuthenticateException;
use MessageBird\Exceptions\BalanceException;
use MessageBird\Objects\Message;

class Sender
{
    /**
     * @var Client
     */
    private $messageBird;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * Sender constructor.
     * @param Client $messageBird
     * @param MessageRepository $messageRepository
     * @param MessageManager $messageManager
     */
    public function __construct(
        Client $messageBird,
        MessageRepository $messageRepository,
        MessageManager $messageManager
    ) {
        $this->messageBird = $messageBird;
        $this->messageRepository = $messageRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * @return Message|null
     *
     * @throws ApplicationException
     */
    public function send(): ?Message
    {
        $messageContainer = $this->messageRepository->findMessageToSend();

        if (empty($messageContainer)) {
            return null;
        }

        try {
            $this->messageManager->updateStatus($messageContainer->_id, MessageContainer::STATUS_PENDING);

            $response = $this->messageBird->messages->create($messageContainer->message);

            $this->messageManager->updateStatus($messageContainer->_id, MessageContainer::STATUS_FINISHED);
        } catch (AuthenticateException|BalanceException $e) {
            $this->messageManager->updateStatus($messageContainer->_id, MessageContainer::STATUS_FAILED);
            throw new ApplicationException('External gateway problem', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            $this->messageManager->updateStatus($messageContainer->_id, MessageContainer::STATUS_FAILED);
            throw new ApplicationException();
        }

        return $response;
    }
}
