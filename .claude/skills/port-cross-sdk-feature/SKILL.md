---
name: port-cross-sdk-feature
description: Ports a feature that already exists in another PubNub SDK (C#, JavaScript, Kotlin, Swift, Python, Go...) or in a cross-SDK design document into the PHP SDK, keeping wire formats and semantics identical. Use when the user points to another SDK's implementation, a PubNub spec or design doc, or shared cross-SDK test vectors.
---

# Port a cross-SDK feature

PubNub SDKs must interoperate: data one SDK writes, the others read. Parity with the reference implementation matters more than idiomatic novelty.

## Workflow

1. **Collect the sources of truth:** the design doc or spec, the reference SDK's implementation and tests, and any shared test vectors. Ask the user for paths if they aren't given. Note anything the spec leaves open.
2. **Extract the contract** as a short list:
   - the wire format: paths, parameters, headers, bodies, byte layouts
   - defaults and validation rules
   - error cases
   - which behaviors are intentionally SDK-specific
3. **Map it onto the PHP structure.** Decide which existing component the feature extends: `CryptoModule` with `Crypto/`, an `Endpoint` subclass, `SubscriptionManager` or `PNConfiguration`. Then decide how it gets registered: `CryptoModule` factories, `PubNub.php` factory methods, or `DataSync.php`.
4. **Adapt to PHP conventions, not the reference language.**
   - Use the PHP exception classes (`PubNubValidationException`, `PubNubResponseParsingException`...) and the SDK's existing error messages.
   - Keep the flow synchronous.
   - Use PHP 8.1 and OpenSSL instead of the reference SDK's libraries.
5. **Check backward compatibility in both directions:** existing PHP users upgrading, and older SDKs reading data this SDK produces. Staged rollouts (decrypt first, encrypt later) must leave defaults unchanged.
6. **Write tests:**
   - Known-answer tests from the shared vectors, inlined as a `public static` data provider (see `tests/unit/CryptoModule/AesGcmCryptorVectorTest.php`). Write non-ASCII characters as `\u{...}` escapes, because shared files are often mangled in transit (emoji turned into `:shortcodes:`). Assert derived values such as hashes and hex so that a wrong literal fails loudly.
   - Behavioral tests mirroring the reference SDK's test cases.
   - Negative tests: tampering, malformed input, wrong keys, superseded formats.
7. **Verify** with the `run-checks` skill. Then summarize the differences from the reference SDK and why each one exists.
