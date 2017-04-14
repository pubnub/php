<?php

namespace Tests\Helpers;


class Stub
{
    const ANY = 'any value';

    protected $initialUrl;
    protected $scheme;
    protected $method;
    protected $host;
    protected $path;

    /** @var string[] */
    protected $query = [];

    /** @var  string */
    protected $body;

    /** @var  string */
    protected $status = "HTTP/1.0 200 OK\r\n";

    /** @var  string */

    /**
     * Stub constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function __toString()
    {
        $queryString = $this->queryString();

        if ($queryString) {
            return $this->path . '?' . $this->queryString();
        } else {
            return $this->path;
        }
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setResponseBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $status
     * @return $this
     * @throws \Exception
     */
    public function setResponseStatus($status)
    {
        if (!is_string($status)) {
            throw new \Exception("Stubbed status should be a string like \"HTTP/1.1 200 OK\"");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return $this
     */
    public function usePost()
    {
        $this->method = "POST";

        return $this;
    }

    /**
     * @param string | array $query
     * @return $this
     */
    public function withQuery($query)
    {
        if (is_string($query)) {
            $queryArray = [];

            parse_str($query, $queryArray);

            $this->query = $queryArray;
        } else {
            $this->query = $query;
        }

        return $this;
    }

    public function isPathMatch($path)
    {
        return $this->path === $path;
    }

    public function isQueryMatch($actualQueryString)
    {
        $actualQuery = [];
        $queryElements = explode("&", $actualQueryString);

        foreach ($queryElements as $element) {
            $keyVal = explode("=", $element);
            $actualQuery[$keyVal[0]] = $keyVal[1];
        }

        foreach ($actualQuery as $key => $value) {
            if (array_key_exists($key, $this->query)) {
                if ($this->query[$key] === static::ANY) {
                    continue;
                }

                if ($this->query[$key] !== $value) {
                    return false;
                }
            } else {
                return false;
            }
        }

        foreach ($this->query as $key => $value) {
            if (!array_key_exists($key, $actualQuery)) {
                return false;
            }
        }

        return true;
    }

    public function queryString()
    {
        $queryArray = [];

        foreach ($this->query as $key => $value) {
            if ($value == static::ANY) {
                $value = "{ANY}";
            };

            $queryArray[] = "$key=$value";
        }

        return join("&", $queryArray);
    }
}

class StubException extends \Exception
{
}
