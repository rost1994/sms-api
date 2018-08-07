<?php
declare(strict_types=1);

namespace Api\Sms\Api\Middleware;

use Api\Sms\Api\Controller\ControllerActionInterface;
use Api\Sms\Api\Controller\MessageSendingControllerAction;
use Api\Sms\Api\Controller\MessageStatusControllerAction;
use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

class Router implements MiddlewareInterface
{
    private const ROUTE_METHODS = 'methods';
    private const ROUTE_ATTRIBUTES = 'attributes';

    /**
     * Array of routes for API
     * Key of array is a regex that appropriate request should correspond
     */
    private const ROUTES = [
        '/^\/api\/message\/$/' => [
            self::ROUTE_METHODS => [
                Request::METHOD_POST => MessageSendingControllerAction::class
            ],
            self::ROUTE_ATTRIBUTES => [],
        ],
        '/^\/api\/message\/(\w+)\/$/' => [
            self::ROUTE_METHODS => [
                Request::METHOD_GET => MessageStatusControllerAction::class
            ],
            self::ROUTE_ATTRIBUTES => ['id'],
        ]
    ];

    /**
     * @var ControllerActionInterface[]
     */
    private $controllerActions = [];

    /**
     * Match ControllerAction based on requested path and method
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     *
     * @return Response
     *
     * @throws ApplicationException
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        foreach (static::ROUTES as $route => $params) {
            if (!$this->isRequestedRoute($route, $params[self::ROUTE_METHODS], $request)) {
                continue;
            }

            $controllerActionName = $params[self::ROUTE_METHODS][$request->getMethod()];

            $controllerAction = $this->controllerActions[$controllerActionName];

            preg_match($route, $request->getPath(), $requestAttributesValues);
            $requestAttributesKeys = $params[self::ROUTE_ATTRIBUTES];
            array_unshift($requestAttributesKeys, 'route_name');

            $request->setAttributes(array_combine($requestAttributesKeys, $requestAttributesValues));

            if (!$controllerAction instanceof ControllerActionInterface) {
                throw new ApplicationException();
            }

            $response = $controllerAction->execute($request);

            return $next($request, $response);
        }


        throw new ApplicationException('Route not found', Response::HTTP_NOT_FOUND);

    }

    /**
     * @param ControllerActionInterface $controllerAction
     *
     * @return $this
     */
    public function pushControllerAction(ControllerActionInterface $controllerAction)
    {
        $this->controllerActions[get_class($controllerAction)] = $controllerAction;

        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @param Request $request
     *
     * @return bool
     *
     * @throws ApplicationException
     */
    private function isRequestedRoute(string $route, array $params, Request $request): bool
    {
        if (preg_match($route, $request->getPath())) {
            if (isset($params[$request->getMethod()])) {
                return true;
            } else {
                throw new ApplicationException('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
            }
        }

        return false;
    }
}
