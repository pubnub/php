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

    public function addStub(PsrStub $stub): self
    {
        $this->stubs[] = $stub;
        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $pathFound = false;
        $queryFound = false;

        foreach ($this->stubs as $stub) {
            $pathFound = $pathFound || $stub->isPathMatch($request->getUri()->getPath());
            $queryFound = $queryFound || $stub->isQueryMatch($request->getUri()->getQuery());

            if ($pathFound && $queryFound) {
                return $stub->getResponse();
            }
        }
        throw new \Exception(
            "No stub matched for:" . $request->getUri()->getPath() . "?" . $request->getUri()->getQuery() .
            (!$pathFound ? "PathNotFound" : "") . (!$queryFound ? "QueryNotFound" : "")
        );
    }
}
