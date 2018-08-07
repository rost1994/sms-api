<?php
declare(strict_types=1);

namespace Api\Sms\Sender\MongoDb\Repository;

use Api\Sms\Sender\Entity\MessageContainer;
use MessageBird\Objects\Message;
use MongoDB\Model\BSONDocument;

class MessageContainerMapper
{
    /**
     * @param BSONDocument $document
     *
     * @return MessageContainer
     */
    public function map(BSONDocument $document): MessageContainer
    {
        $messageContainer = new MessageContainer();

        $message = new Message();

        $message->type = $document->message->type;
        $message->originator = $document->message->originator;
        $message->body = $document->message->body;

        /** @var BSONDocument $typeDetails */
        $typeDetails = $document->message->typeDetails;
        $message->typeDetails = $typeDetails->getArrayCopy();
        $message->datacoding = $document->message->datacoding;

        /** @var BSONDocument $recipients */
        $recipients = $document->message->recipients;
        $message->recipients = $recipients->getArrayCopy();

        $messageContainer->message = $message;
        $messageContainer->status = $document->status;

        /** @var BSONDocument $creationDate */
        $creationDate = $document->creationDate;
        $messageContainer->creationDate = \DateTime::createFromFormat('Y-m-d H:i:s.u', $creationDate->date);
        $messageContainer->groupId = $document->groupId;

        $messageContainer->_id = $document->_id;

        return $messageContainer;
    }
}
