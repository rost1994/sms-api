<?php
declare(strict_types=1);

namespace Api\Sms\Sender;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Sender\Entity\MessageContainer;
use Api\Sms\Sender\MongoDb\Repository\MessageManager;
use MessageBird\Objects\Message;

class SmsHandler
{
    /**
     * @var MessageSplitter
     */
    private $messageSplitter;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * Sender constructor.
     * @param MessageSplitter $messageSplitter
     * @param MessageManager $messageManager
     */
    public function __construct(MessageSplitter $messageSplitter, MessageManager $messageManager)
    {
        $this->messageSplitter = $messageSplitter;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Message $message
     *
     * @return string
     *
     * @throws ApplicationException
     */
    public function handle(Message $message): string
    {
        $batchMessages = $this->messageSplitter->split($message);
        $uuid = uniqid();

        foreach ($batchMessages as $messageInSeries) {
            $messageContainer = new MessageContainer();
            $messageContainer->status = MessageContainer::STATUS_WAITING;
            $messageContainer->message = $messageInSeries;
            $messageContainer->creationDate = new \DateTime();
            $messageContainer->groupId = $uuid;


            $this->messageManager->store($messageContainer, $uuid);
        }

        return $uuid;
    }
}
