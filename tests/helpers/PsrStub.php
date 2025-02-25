<?php

namespace PubNubTests\helpers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use PubNub\PubNubUtil;

class PsrStub
{
    private string $path;
    private string $query;
    private mixed $response;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function withQuery(array $query): self
    {
        $this->query = http_build_query($query);

        return $this;
    }

    public function setResponseBody(string $response)
    {
        $this->response = $response;
    }

    public function isPathMatch(string $path): bool
    {
        return $this->path === $path;
    }

    public function isQueryMatch(string $query): bool
    {
        return $this->query === $query;
    }

    public function getResponse(): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'application/json'], $this->response);
    }
}
