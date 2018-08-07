<?php
declare(strict_types=1);

namespace Api\Sms\Api\Http;

class Request
{
    public const ACCEPTED_CONTENT_TYPES = [
        self::CONTENT_TEXT,
        self::CONTENT_JSON,
        self::CONTENT_FORM,
        self::CONTENT_FORM_URLENCODED
    ];

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_DELETE = 'DELETE';

    public const CONTENT_TEXT = 'text/plain';
    public const CONTENT_JSON = 'application/json';
    public const CONTENT_FORM = 'multipart/form-data';
    public const CONTENT_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * Collection of request headers
     *
     * @var string[]
     */
    private $headers = [];

    /**
     * Requested method
     *
     * @var string
     */
    private $method = '';

    /**
     * Requested path
     *
     * @var string
     */
    private $path = '';

    /**
     * GET parameters collection
     *
     * @var array
     */
    private $query = [];

    /**
     * @var array
     */
    private $server = [];

    /**
     * POST parameters collection
     *
     * @var array
     */
    private $body = [];

    /**
     * Dynamic parts of path for each route.
     *
     * @var string[]
     */
    private $attributes = [];

    /**
     * Request constructor.
     * @param string[] $headers
     * @param string $method
     * @param string $path
     * @param array $query
     * @param array $body
     * @param array $server
     */
    public function __construct(array $headers, string $method, string $path, array $query, array $body, array $server)
    {
        $this->headers = $headers;
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
    }

    /**
     * Check either passed Content-Typ available or not
     *
     * @param string $actualContentType
     *
     * @return bool
     */
    public static function isContentTypeAcceptable(string $actualContentType): bool
    {
        foreach (static::ACCEPTED_CONTENT_TYPES as $acceptedContactType) {
            if (false !== strpos($actualContentType, $acceptedContactType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getServer(): array
    {
        return $this->server;
    }

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string[] $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes): Request
    {
        $this->attributes = $attributes;

        return $this;
    }
}