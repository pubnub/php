<?php

namespace PubNubTests\helpers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class PsrStub
{
    private string $path;
    private string $query;
    private mixed $responseBody;
    private ?int $responseStatus;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param String[] $query
     * @return PsrStub
     */
    public function withQuery(array $query): self
    {
        $this->query = http_build_query($query);
        $this->query = str_replace(['%2C', '%25'], [',', '%'], $this->query);
        return $this;
    }

    public function setResponseBody(string $responseBody): self
    {
        $this->responseBody = $responseBody;
        return $this;
    }

    public function setResponseStatus(int $responseStatus): self
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }

    public function isPathMatch(string $path): bool
    {
        if ($this->path !== $path) {
            print("\nThis: {$this->path}\n");
            print("\nThat: {$path}\n");
        }
        return $this->path === $path;
    }

    public function isQueryMatch(string $query): bool
    {
        $expected = [];
        $actual = [];
        parse_str($this->query, $expected);
        ksort($expected);
        parse_str($query, $actual);
        ksort($actual);
        $expected = http_build_query($expected);
        $actual = http_build_query($actual);

        if ($expected !== $actual) {
            print("\nThis: {$expected}\n");
            print("\nThat: {$actual}\n");
        }
        return $expected === $actual;
    }

    public function getResponse(): ResponseInterface
    {
        if (isset($this->responseStatus)) {
            return new Response($this->responseStatus, ['Content-Type' => 'application/json'], $this->responseBody);
        }
        return new Response(200, ['Content-Type' => 'application/json'], $this->responseBody);
    }
}
