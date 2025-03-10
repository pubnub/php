<?php

namespace PubNubTests\helpers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;

class PsrStubClient implements ClientInterface
{
    /**
     * @var PsrStub[]
     */
    private $stubs = [];

    public function stubFor(string $url): PsrStub
    {
        $stub = new PsrStub($url);
        $this->addStub($stub);
        return $stub;
    }

    public function addStub(PsrStub $stub)
    {
        $this->stubs[] = $stub;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        foreach ($this->stubs as $stub) {
            if (
                $stub->isPathMatch($request->getUri()->getPath())
                && $stub->isQueryMatch($request->getUri()->getQuery())
            ) {
                return $stub->getResponse();
            }
        }
        throw new \Exception(
            "No stub matched for:"
            . $request->getUri()->getPath() . "?" . $request->getUri()->getQuery()
            . "\nChecks: "
            . ($stub->isPathMatch($request->getUri()->getPath()) ? "PathMatch" : "PathMismatch")
            . ($stub->isQueryMatch($request->getUri()->getQuery()) ? "QueryMatch" : "QueryMismatch")
        );
    }
}
