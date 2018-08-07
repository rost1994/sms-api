<?php
declare(strict_types=1);

namespace Api\Sms\Sender\MongoDb\Repository;

use Api\Sms\Sender\Entity\MessageContainer;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;

class MessageManager
{
    /**
     * @var Client
     */
    private $mongo;

    /**
     * MessageManager constructor.
     * @param Client $mongo
     */
    public function __construct(Client $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Stores a message into DB for scheduling sending.
     *
     * @param MessageContainer $messageContainer
     *
     * @return ObjectId
     */
    public function store(MessageContainer $messageContainer): ObjectId
    {
        $insertedResult = $this->mongo->smsApi->messages->insertOne($messageContainer);

        return $insertedResult->getInsertedId();
    }

    /**
     * @param ObjectId $id
     * @param string $status
     */
    public function updateStatus(ObjectId $id, string $status): void
    {
        $this->mongo->smsApi->messages->findOneAndUpdate(['_id' => $id], ['$set' => ['status' => $status]]);

        return;
    }
}
