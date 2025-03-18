<?php

// @phpstan-ignore-file
// phpcs:ignoreFile
require_once 'vendor/autoload.php';

use PubNub\PubNub;
use PubNub\PNConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private int $statusCode;
    private array $headers;
    private StreamInterface $body;

    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = new Stream($body);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return '';
    }

    public function getProtocolVersion(): string
    {
        return '';
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function withHeader($name, $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withAddedHeader($name, $value): self
    {
        $this->headers[$name][] = $value;
        return $this;
    }

    public function withoutHeader($name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function withProtocolVersion($version): self
    {
        return $this;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[$name]);
    }

    public function getHeader($name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->headers[$name] ?? []);
    }

    public function withBody(StreamInterface $body): self
    {
        $this->body = $body;
        return $this;
    }
}

class Stream implements StreamInterface
{
    private $stream;
    private $size;

    public function __construct(string $content = '')
    {
        $this->stream = fopen('php://temp', 'r+');
        fwrite($this->stream, $content);
        rewind($this->stream);
        $this->size = strlen($content);
    }

    public function __toString(): string
    {
        return stream_get_contents($this->stream, -1, 0);
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        return $result;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        return ftell($this->stream);
    }

    public function eof(): bool
    {
        return feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        fseek($this->stream, $offset, $whence);
    }

    public function rewind(): void
    {
        rewind($this->stream);
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write($string): int
    {
        $result = fwrite($this->stream, $string);
        $this->size += strlen($string);
        return $result;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        return fread($this->stream, $length);
    }

    public function getContents(): string
    {
        return stream_get_contents($this->stream);
    }

    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        if ($key === null) {
            return $meta;
        }
        return $meta[$key] ?? null;
    }
}

class CustomClient implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, (string) $request->getUri());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = $name . ': ' . $value;
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($request->getBody()->getSize() > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, (string) $request->getBody());
        }
        print("\n doing request...");
        $responseBody = curl_exec($ch);
        print("done\n\n");
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseBody === false) {
            throw new \RuntimeException('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return new Response($responseCode, [], $responseBody);
    }
}

$config = new PNConfiguration();
$config->setPublishKey('demo');
$config->setSubscribeKey('demo');
$config->setUuid('example');

$pubnub = new PubNub($config);
$client = new CustomClient();
$pubnub->setClient($client);

$time = $pubnub->time()->sync();
print_r($time->getTimetoken());
