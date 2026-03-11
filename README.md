# Shadow Logger Bundle

![CI](https://github.com/aubes/shadow-logger-bundle/actions/workflows/php.yml/badge.svg)

This Symfony bundle provides a Monolog processor to transform log data in order to respect GDPR or anonymize sensitive data.

It allows IP anonymization, hashing, encryption, or removal of sensitive fields in logs.

## Requirements

- PHP >= 8.1
- Symfony >=6.1, 7, 8
- Monolog 3

## Installation

```shell
composer require aubes/shadow-logger-bundle
```

## Configuration

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    # Add "shadow-debug" in "extra" when a transformer throws an exception.
    # Recommended to use '%kernel.debug%' so it is only active in development.
    debug: '%kernel.debug%'

    # When a transformer throws an exception:
    #   true  → the field value is set to null
    #   false → the original (untransformed) value is kept
    strict: true

    # Register ShadowProcessor on handlers OR channels (not both).
    # Scoping to specific handlers/channels is recommended for performance.
    handlers: ['app']
    #channels: ['app']

    # Salt used by the "hash" transformer (see Hash transformer section).
    encoder:
        salt: '%env(SHADOW_LOGGER_ENCODER_SALT)%'

    mapping:
        # Fields to transform in the log "context"
        context:
            user_ip:        ['ip']
            user_name:      ['hash']
            user_birthdate: ['remove']

        # Fields to transform in the log "extra"
        extra:
            custom_field: ['remove']
```

## Choosing between hashing and encryption

Both the `hash` and `encrypt` transformers protect sensitive data in logs, but they serve different purposes. Choosing the right one depends on what you need to do with the data after it is logged.

### Hashing (`hash`)

Hashing is a **one-way, irreversible** operation. The original value cannot be recovered from the hash.

**Use it when:**
- You do not need to read back the original value
- You want to correlate log entries belonging to the same user (e.g. trace a user across multiple requests) without storing their identity — the same input always produces the same hash
- You want to pseudonymize data in compliance with GDPR

**Limitations:**
- If the space of possible values is small (e.g. an IP address), an attacker with access to the logs could reconstruct the original values through brute force
- The salt must be kept secret; rotating it means previously hashed values can no longer be correlated with new ones
- You cannot fulfill a GDPR "right of access" request using only the hashed value

**Configuration:**

```yaml
shadow_logger:
    encoder:
        algo:   'sha256'  # any algorithm from hash_algos()
        salt:   '%env(SHADOW_LOGGER_ENCODER_SALT)%'
        binary: false
```

> Always configure a `salt` to prevent rainbow table attacks. Store it as a secret environment variable.

### Encryption (`encrypt`)

Encryption is a **reversible** operation. The original value can be recovered using the key and the IV stored alongside it.

**Use it when:**
- You may need to read back the original value later (e.g. to respond to a GDPR right of access or right of erasure request, or to debug a production issue with proper authorization)
- You want to store sensitive data in logs in a protected form without losing it permanently

**Limitations:**
- Requires secure key management: if the key is compromised, all encrypted log entries are exposed
- Key rotation is complex: old entries encrypted with a previous key can no longer be decrypted with the new one
- The presence of the IV in the log reveals that the original field was non-empty, which may itself be sensitive
- More computationally expensive than hashing

### Quick comparison

| | `hash` | `encrypt` | `remove` |
|---|---|---|---|
| Reversible | No | Yes (with key) | No |
| Correlate entries | Yes (same input → same output) | No (IV is random) | No |
| Protect against brute force | With a strong salt | Yes | Yes |
| GDPR right of access | No | Yes | No |
| Key management required | Salt only | Key + IV | None |

## Transformers

The following transformers are available out of the box:

| Alias | Description |
|-------|-------------|
| `ip` | Anonymizes an IPv4 or IPv6 address |
| `hash` | Hashes the value using the configured algorithm |
| `string` | Casts a scalar or `Stringable` object to string |
| `remove` | Replaces the value with `--obfuscated--` |
| `encrypt` | Encrypts the value (requires encryptor configuration) |
| `truncate` | Masks the middle of a value, keeping start and/or end visible (requires truncator configuration) |

### Chaining transformers

Transformers can be chained. They are applied in order, the output of one becoming the input of the next. For example, to hash a `Stringable` object:

```yaml
shadow_logger:
    mapping:
        context:
            custom_field: ['string', 'hash']
```

### Hash transformer

The `hash` transformer uses the configured encoder. See the [Hashing section](#hashing-hash) above for configuration options.

### Encrypt transformer

The `encrypt` transformer supports two modes: a built-in encryptor based on OpenSSL, or a custom implementation.

#### Built-in encryptor

```yaml
shadow_logger:
    encryptor:
        key:    '%env(SHADOW_LOGGER_ENCRYPTOR_KEY)%'
        cipher: 'aes-256-cbc' # optional, default: aes-256-cbc
```

The key must be kept secret and should be provided via an environment variable. The cipher can be any algorithm supported by OpenSSL (`openssl_get_cipher_methods()`).

#### Custom encryptor

If you need a different encryption strategy, implement [`EncryptorInterface`](src/Encryptor/EncryptorInterface.php):

```php
// src/Encryptor/EncryptorAdapter.php
namespace App\Encryptor;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;

class EncryptorAdapter implements EncryptorInterface
{
    public function encrypt(string $data, string $iv): string
    {
        // your encryption logic
        return $encryptedValue;
    }

    public function generateIv(): string
    {
        // generate a random IV
        return $iv;
    }
}
```

Register it as a service (if not using autoconfiguration):

```yaml
# config/services.yaml
services:
    App\Encryptor\EncryptorAdapter: ~
```

Then reference it by its service ID:

```yaml
shadow_logger:
    encryptor: 'App\Encryptor\EncryptorAdapter'
```

#### Output format

In both cases, the transformer replaces the field value with:

```php
[
    'iv'    => 'abc123', // base64-encoded IV used during encryption
    'value' => '...',    // encrypted value
]
```

### Truncate transformer

The `truncate` transformer masks the middle of a value while keeping a configurable number of characters visible at the start and/or end. It is useful for partially revealing values like card numbers, email addresses, or tokens.

Named variants are declared under `truncators`. Each variant becomes available as a transformer alias: `default` → `truncate`, others → `truncate_{name}`.

```yaml
shadow_logger:
    truncators:
        default:            # alias: "truncate"
            keep_start: 2
            keep_end:   2
            mask:       '***'
        card:               # alias: "truncate_card"
            keep_start: 4
            keep_end:   4
            mask:       '****'
        email:              # alias: "truncate_email"
            keep_start: 1
            keep_end:   0
            mask:       '***'

    mapping:
        context:
            card_number: ['truncate_card']    # 4242424242424242 → 4242****4242
            email:       ['truncate_email']   # john@example.com → j***
            token:       ['truncate']         # abcdef1234 → ab***34
```

| Option | Description | Default |
|--------|-------------|---------|
| `keep_start` | Number of characters to keep at the beginning | `2` |
| `keep_end` | Number of characters to keep at the end | `2` |
| `mask` | String used to replace the hidden part | `***` |

> If the value is shorter than or equal to `keep_start + keep_end`, it is replaced entirely by the mask.

## Mapping

### Nested fields

Field names can use dot notation to target nested array keys.

Given the following `extra` structure:

```php
'user' => [
    'id'   => 42,
    'name' => [
        'first' => 'John',
        'last'  => 'Doe',
    ],
    'ip'   => '1.2.3.4',
]
```

You can map nested fields like this:

```yaml
shadow_logger:
    mapping:
        extra:
            user.ip:         ['ip']
            user.name.first: ['hash']
            user.name.last:  ['remove']
```

> **Note:** Dot notation uses the Symfony PropertyAccessor internally, which is slower than direct key access. Prefer flat field names when possible.

## Custom transformer

Implement [`TransformerInterface`](src/Transformer/TransformerInterface.php):

```php
// src/Transformer/CustomTransformer.php
namespace App\Transformer;

use Aubes\ShadowLoggerBundle\Transformer\TransformerInterface;

class CustomTransformer implements TransformerInterface
{
    public function transform(mixed $data): mixed
    {
        // transform and return the value
        return $data;
    }
}
```

Register it as a service with the `shadow_logger.transformer` tag and an `alias`:

```yaml
# config/services.yaml
services:
    App\Transformer\CustomTransformer:
        tags:
            - { name: 'shadow_logger.transformer', alias: 'custom' }
```

The `alias` is the name used in the `mapping` configuration:

```yaml
shadow_logger:
    mapping:
        context:
            some_field: ['custom']
```
