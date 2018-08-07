<?php
declare(strict_types=1);

namespace Api\Sms\Sender\MongoDb\Repository;

use Api\Sms\Sender\Entity\MessageContainer;
use MongoDB\Client;

class MessageRepository
{
    /**
     * @var Client
     */
    private $mongo;

    /**
     * @var MessageContainerMapper
     */
    private $messageContainerMapper;

    /**
     * MessageRepository constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->mongo = $client;
        $this->messageContainerMapper = new MessageContainerMapper();
    }

    /**
     * @return MessageContainer|null
     */
    public function findMessageToSend(): ?MessageContainer
    {
        $result = $this->mongo->smsApi->messages->findOne(
            ['status' => MessageContainer::STATUS_WAITING],
            ['sort' => ['creationDate' => 1]]
        );

        if (!$result) {
            return null;
        }

        return $this->messageContainerMapper->map($result);
    }

    /**
     * @param string $groupId
     *
     * @return MessageContainer[]
     */
    public function findMessagesByGroupId(string $groupId): array
    {
        $messageContainers = [];

        $results = $this->mongo->smsApi->messages->find(
            ['groupId' => $groupId]
        );

        foreach ($results as $result) {
            $messageContainers[] = $this->messageContainerMapper->map($result);
        }

        return $messageContainers;
    }
}
