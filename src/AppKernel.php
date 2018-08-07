<?php
declare(strict_types=1);

namespace Api\Sms;

use Api\Sms\Api\Controller\MessageSendingControllerAction;
use Api\Sms\Api\Controller\MessageStatusControllerAction;
use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Factory\ResponseFactory;
use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;
use Api\Sms\Api\Middleware\Authenticator;
use Api\Sms\Api\Middleware\MiddlewareInterface;
use Api\Sms\Api\Middleware\Router;
use Api\Sms\Sender\MessageSplitter;
use Api\Sms\Sender\MongoDb\Repository\MessageManager;
use Api\Sms\Sender\MongoDb\Repository\MessageRepository;
use Api\Sms\Sender\Sender;
use Api\Sms\Sender\SmsHandler;
use MessageBird\Client as MessageBirdClient;
use MessageBird\Objects\Message;
use MongoDB\Client as MongoDbClient;

/**
 * Class AppKernel
 * @package Api\Sms
 */
class AppKernel
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareQueue = [];

    /**
     * @var Sender
     */
    private $sender;

    /**
     * Simplest initialization of application services
     * @param array $config
     *
     * @return AppKernel
     */
    public function boot(array $config): AppKernel
    {
        $authMiddleware = new Authenticator();
        $routerMiddleware = new Router();

        $client = new MongoDbClient($config['mongo_endpoint']);
        $messageRepository = new MessageRepository($client);
        $messageManager = new MessageManager($client);

        $messageBird = new MessageBirdClient($config['message_bird_api_key']);
        $this->sender = new Sender($messageBird, $messageRepository, $messageManager);
        $messageSplitter = new MessageSplitter();
        $sender = new SmsHandler($messageSplitter, $messageManager);

        $responseFactory = new ResponseFactory();

        $messageSendingAction = new MessageSendingControllerAction($sender, $responseFactory);
        $messageStatusAction = new MessageStatusControllerAction($responseFactory, $messageRepository);

        $routerMiddleware
            ->pushControllerAction($messageSendingAction)
            ->pushControllerAction($messageStatusAction);

        $this
            ->addMiddleware($authMiddleware)
            ->addMiddleware($routerMiddleware);

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ApplicationException
     */
    public function handle(Request $request): Response
    {
        $handler = function($request, $response) use (&$handler) {
            $middleware = array_shift($this->middlewareQueue);

            if (null === $middleware) {
                return $response;
            }

            return $middleware($request, $response, $handler);
        };

        return $handler($request, new Response);
    }

    /**
     * @return Message|null
     *
     * @throws ApplicationException
     */
    public function sendSms(): ?Message
    {
        return $this->sender->send();
    }

    /**
     * @param MiddlewareInterface $middleware
     *
     * @return AppKernel
     */
    private function addMiddleware(MiddlewareInterface $middleware): AppKernel
    {
        $this->middlewareQueue[] = $middleware;

        return $this;
    }
}
