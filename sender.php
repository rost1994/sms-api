<?php
declare(strict_types=1);

/*
 * Used for sending SMS to MessageBird
 * sender.php should be executed by crontab every second
 */

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\AppKernel;

require_once(__DIR__ . '/vendor/autoload.php');

$config = require_once(__DIR__ . '/config/config.php');

$kernel = new AppKernel;

try {
    $kernel->boot($config);

    $response = $kernel->sendSms();
} catch (ApplicationException $e) {
    echo 'Error occured! Message:'. PHP_EOL . $e->getMessage() . PHP_EOL;
    exit();
}

if (!$response) {
    echo 'SMS pool is empty' . PHP_EOL;
} else {
    echo 'SMS sent successfully' . PHP_EOL;
}

