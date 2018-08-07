<?php
declare(strict_types=1);

namespace Api\Sms\Api\Http;

/**
 * Class Response
 * @package Api\Sms\Api\Http
 */
class Response
{
    public const HTTP_OK = 200;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @var string[]
     */
    private $headers = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * Response constructor.
     * @param int $statusCode
     * @param string $content
     * @param string[] $headers
     */
    public function __construct(int $statusCode = 200, string $content = '', array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string[] $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers): Response
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): Response
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function send(): void
    {
        foreach ($this->getHeaders() as $headerName => $headerValue) {
            header($headerName.': '.$headerValue, false, $this->getStatusCode());
        }

        http_response_code($this->getStatusCode());

        echo $this->getContent();

        fastcgi_finish_request();
    }
}