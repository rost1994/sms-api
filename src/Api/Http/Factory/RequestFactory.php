<?php
declare(strict_types=1);

namespace Api\Sms\Api\Http\Factory;

use Api\Sms\Api\Exception\ApplicationException;
use Api\Sms\Api\Http\Request;
use Api\Sms\Api\Http\Response;

class RequestFactory
{
    /**
     * @throws ApplicationException
     *
     * @return Request
     */
    public function createFromGlobals(): Request
    {
        $headers = $this->getHeaders();
        $method = $_SERVER['REQUEST_METHOD'] ?? Request::METHOD_GET;

        if ($method !== Request::METHOD_GET && !Request::isContentTypeAcceptable((string)$headers['Content-Type'])) {
            throw new ApplicationException(
                sprintf(
                    'Unsupported Content-Type. Possible values are: %s',
                    implode(', ', Request::ACCEPTED_CONTENT_TYPES)
                ),
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        }

        if (empty($_POST)) {
            $body = $this->getJsonBody();
        } else {
            $body = $_POST;
        }

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return new Request($headers, $method, $path, $_GET, $body, $_SERVER);
    }

    /**
     * Takes headers from SERVER superglobal and pack them into array
     *
     * @return string
     */
    private function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }

            $header = str_replace(
                ' ',
                '-',
                ucwords(str_replace('_', ' ', strtolower(substr($key, 5))))
            );

            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * @return array
     */
    private function getJsonBody(): array
    {
        $stream = fopen('php://input', 'r');

        $content = stream_get_contents($stream);

        return json_decode($content, true) ?? [];
    }
}
