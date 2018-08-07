<?php declare(strict_types=1);

namespace Api\Sms\Sender\Entity;

use MessageBird\Objects\Message;
use MongoDB\BSON\ObjectId;

class MessageContainer
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    /**
     * @var ObjectId
     */
    public $_id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var Message
     */
    public $message;

    /**
     * @var \DateTime
     */
    public $creationDate;

    /**
     * Used to retrieve group of concatenated messages
     *
     * @var string
     */
    public $groupId;

    /**
     * MessageContainer constructor.
     */
    public function __construct()
    {
        $this->_id = new ObjectId();
    }
}
