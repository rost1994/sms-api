<?php
declare(strict_types=1);

/*
 * Used as Api entry point
 * On send request accept messages and store them to MongoDb
 * On status request get message status from MongoDb
 */

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Factory\RequestFactory;
use Api\Sms\Api\Http\Factory\ResponseFactory;
use Api\Sms\AppKernel;
use Api\Sms\ErrorHandler;

require_once(__DIR__ . '/vendor/autoload.php');

$config = require_once(__DIR__ . '/config/config.php');

$kernel = new AppKernel;

try {
    $request = (new RequestFactory())->createFromGlobals();

    $kernel->boot($config);

    $response = $kernel->handle($request);
} catch (ApplicationException $e) {
    $response = (new ErrorHandler(new ResponseFactory()))->getResponseFromError($e);
}

$response->send();
