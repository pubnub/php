---
name: add-endpoint
description: Adds a new REST API endpoint to the PubNub PHP SDK, covering the endpoint class, client factory method, operation type, result model, tests and docs example. Use when implementing a new PubNub API call, adding a missing endpoint, or porting an endpoint from another PubNub SDK.
---

# Add an endpoint

Copy this checklist and track progress:

```text
- [ ] 1. API contract understood
- [ ] 2. Endpoint class
- [ ] 3. Result model
- [ ] 4. Operation type and factory method
- [ ] 5. Tests
- [ ] 6. Example snippet
- [ ] 7. Checks pass (run-checks skill)
```

## 1. Understand the API contract

- Find the REST path, HTTP method, query parameters, body, response and error shapes. Sources: the user's spec, other PubNub SDKs, or <https://www.pubnub.com/docs>. Don't guess field names.
- Pick the closest existing endpoint as a template. For the plain property-driven style, use `src/PubNub/Endpoints/MessageActions/AddMessageAction.php`. Otherwise extend a feature base class: `ObjectsCollectionEndpoint`, `FileSharingEndpoint`, `PushEndpoint` or `DataSyncEndpoint`.

## 2. Endpoint class: `src/PubNub/Endpoints/<Feature>/<Name>.php`

```php
<?php

namespace PubNub\Endpoints\Feature;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Feature\PNDoThingResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;

class DoThing extends Endpoint
{
    protected const PATH = "/v1/feature/sub-key/%s/channel/%s";

    protected bool $endpointAuthRequired = true;
    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNDoThingOperation;
    protected string $endpointName = "Do Thing";

    protected ?string $channel = null;
    protected ?int $limit = null;

    public function __construct(PubNub $pubnub)
    {
        parent::__construct($pubnub);
        $this->endpointRequestTimeout = $pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
        $this->endpointConnectTimeout = $pubnub->getConfiguration()->getConnectTimeout();
    }

    public function channel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        if ($this->channel === null || $this->channel === '') {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @return array<string, string>
     */
    protected function customParams(): array
    {
        $params = [];
        if ($this->limit !== null) {
            $params['limit'] = (string)$this->limit;
        }
        return $params;
    }

    protected function buildData(): ?string
    {
        return null;
    }

    protected function buildPath(): string
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode((string)$this->channel)
        );
    }

    public function sync(): PNDoThingResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, mixed> $json
     */
    protected function createResponse($json): PNDoThingResult
    {
        return PNDoThingResult::fromJson($json);
    }
}
```

- **Bodies (POST, PATCH, PUT):** `buildData()` returns `PubNubUtil::writeValueAsString($body)`, and `customHeaders()` adds `'Content-Type' => 'application/json'`.
- **Raw or empty responses:** set `protected const RESPONSE_IS_JSON = false;` or `protected const RESPONSE_MAY_BE_EMPTY = true;`.
- **Query values:** only `pnsdk`, `uuid`, `auth`, `channel`, `channel-group` and `category` are URL-encoded centrally. Other keys go out exactly as `customParams()` returns them.
- **Parameter types:** keep `createResponse($json)` untyped. Adding a native parameter type to an untyped parent method is a fatal error, so use a docblock.

## 3. Result model: `src/PubNub/Models/Consumer/<Feature>/PN<Name>Result.php`

```php
<?php

namespace PubNub\Models\Consumer\Feature;

class PNDoThingResult
{
    protected int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * @param array<string, mixed> $json
     */
    public static function fromJson(array $json): self
    {
        return new self((int)($json['payload']['count'] ?? 0));
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
```

## 4. Operation type and factory method

- **Operation type:** add `const PNDoThingOperation = <n>;` to `src/PubNub/Enums/PNOperationType.php`, where `<n>` is the current maximum plus one. Find the maximum with grep and never reuse a value.
- **Factory method:** in `src/PubNub/PubNub.php`, add the `use` import and `public function doThing(): DoThing { return new DoThing($this); }` next to the methods of the same feature. Data Sync endpoints go in `src/PubNub/DataSync.php` instead.

## 5. Tests (offline first)

- **Request building and validation:** add a functional test (`tests/functional/...`) with an in-file `DoThingExposed extends DoThing` that makes `buildPath()`/`buildParams()` public. Assert the path, the query (including `pnsdk` and `uuid`) and each validation message.
- **Response parsing:** add an integrational test that stubs HTTP like `tests/integrational/HereNowTest.php` does (`stubFor($path)->withQuery([...])->setResponseBody($json)`). Include an error response.
- **Model edge cases:** add unit tests under `tests/unit/`.
- **Live happy path:** only if the user wants one. It must skip when the required environment variables are missing.

## 6. Example snippet

Add a `// snippet.<name>` ... `// snippet.end` block to the feature's file in `examples/`, using `demo` keys. Make sure the matching `tests/Examples/*Test.php` runs that file.

## 7. Verify

Follow the `run-checks` skill. Then propose a one-line changelog entry for the PR description. Don't edit `CHANGELOG.md` or `.pubnub.yml`; the release bot writes them.
