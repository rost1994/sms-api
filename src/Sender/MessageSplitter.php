<?php
declare(strict_types=1);

namespace Api\Sms\Sender;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Response;
use MessageBird\Objects\Message;

class MessageSplitter
{
    private const MESSAGE_LIMITS = [
        Message::DATACODING_PLAIN => 160,
        Message::DATACODING_UNICODE => 70,
    ];
    private const USER_DATA_HEADER_LENGTH = 7;

    /**
     * @param Message $message
     *
     * @return Message[]
     *
     * @throws ApplicationException
     */
    public function split(Message $message): array
    {
        $messages = [];

        $encoding = $this->guessEncoding($message->body);
        $message->datacoding = $encoding;
        $message->type = Message::TYPE_BINARY;

        $messagesContent = $this->splitBody($message->body, $encoding);

        if (count($messagesContent) === 1) {
            $messages = [$message];
        } else {
            $reference = rand(0, 255);

            $messageIndex = 1;
            $messagesNumber = count($messagesContent);

            foreach ($messagesContent as $messageContent) {
                $messagePart = clone $message;
                $messagePart->body = $messageContent;
                $messagePart->typeDetails = [
                    'udh' => $this->getUserDataHeader($reference, $messagesNumber, $messageIndex)
                ];

                $messages[] = $messagePart;

                $messageIndex++;
            }
        }

        return $messages;
    }

    /**
     * @param string $messageBody
     * @param string $encoding
     *
     * @return string[]
     *
     * @throws ApplicationException
     */
    private function splitBody(string $messageBody, string $encoding): array
    {
        $maxLength = static::MESSAGE_LIMITS[$encoding] ?? 70;
        if (mb_strlen($messageBody) > $maxLength) {
            $maxLength -= static::USER_DATA_HEADER_LENGTH;
        }
        preg_match_all("/.{1,{$maxLength}}/su", $messageBody, $matches);

        $messages = $matches[0];

        $messagesNumber = count($messages);

        if ($messagesNumber > 9) {
            throw new ApplicationException('Message too long', Response::HTTP_BAD_REQUEST);
        }

        foreach ($messages as &$messageBody) {
            $messageBody = $this->getBinaryString($messageBody);
        }
        unset($messageBody);

        return $messages;
    }

    /**
     * Return encoding for MessageBird
     * If string contains GSM 03.38 characters only it is `plain` encoding
     * Otherwise - `unicode`
     *
     * @param string $message
     *
     * @return string
     */
    private function guessEncoding(string $message)
    {
        $gsm0338pattern = '/^[\x{20}-\x{7E}£¥èéùìòÇ\rØø\nÅåΔ_ΦΓΛΩΠΨΣΘΞ\x{1B}ÆæßÉ ¤¡ÄÖÑÜ§¿äöñüà\x{0C}€]*$/u';

        if (preg_match($gsm0338pattern, $message)) {
            return Message::DATACODING_PLAIN;
        } else {
            return Message::DATACODING_UNICODE;
        }
    }

    /**
     * @param int $reference
     * @param int $messagesNumber
     * @param int $messageIndex
     *
     * @return string
     */
    private function getUserDataHeader(int $reference, int $messagesNumber, int $messageIndex)
    {
        return sprintf(
            '050003%s%s%s',
            $this->getDecHexSting($reference),
            $this->getDecHexSting($messagesNumber),
            $this->getDecHexSting($messageIndex)
        );
    }

    /**
     * @param int $referenceNumber
     *
     * @return string
     */
    private function getDecHexSting(int $referenceNumber): string
    {
        return str_pad(dechex($referenceNumber), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function getBinaryString(string $string): string
    {
        return unpack('H*', $string)[1];
    }
}
