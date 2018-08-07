<?php
declare(strict_types=1);

namespace Api\Sms\Api\Middleware;

use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

class Authenticator implements MiddlewareInterface
{
    private const USERNAME = 'test';
    // 123qwe
    private const PASSWORD = '$2y$10$T5IFf6IPyhQu0JJY0vs22u5rJc6sRw7LqS.2PgQMFP5Q.mNAlDK0e';

    /**
     * Used for authentication. HTTP basic auth is just for example
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $username = $request->getServer()['PHP_AUTH_USER'] ?? '';
        $password = $request->getServer()['PHP_AUTH_PW'] ?? '';

        if (!$this->authenticate($username, $password)) {
            $response = new Response(
                Response::HTTP_UNAUTHORIZED,
                '',
                ['WWW-Authenticate' => 'Basic realm="Authentication needed"']
            );

            return $response;
        }

        return $next($request, $response);
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    protected function authenticate(string $username, string $password): bool
    {
        return $username === Authenticator::USERNAME && password_verify($password, Authenticator::PASSWORD);
    }
}
