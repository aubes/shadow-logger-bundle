# Shadow Logger Bundle

![CI](https://github.com/aubes/shadow-logger-bundle/actions/workflows/php.yml/badge.svg)

This Symfony bundle provides a monolog processor to transform log data, in order to respect GDPR or to anonymize sensitive data.

It allows Ip anonymization, encoding or removing data in the log.

## Installation

```shell
composer require aubes/shadow-logger-bundle
```

## Configuration

The configuration looks as follows :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    # If enabled, add "shadow-debug" on "extra" with debug information when exception occurred
    debug:  '%kernel.debug%'

    # If enabled, remove value when exception occurred
    strict: true

    # Register ShadowProcessor on channels or handlers, not both
    # To configure channels or handlers is recommended for performance reason
    # Logging channels the ShadowProcessor should be pushed to
    handlers: ['app']

    # Logging handlers the ShadowProcessor should be pushed to
    #channels: ['app']

    encoder:
        salt: '%env(SHADOW_LOGGER_ENCODER_SALT)%'
    
    mapping:
        # Context fields
        context:
            custom_field: [] # Array of Transformer aliases

            # Examples:
            user_ip: ['ip']
            user_name: ['hash']
            user_birthdate: ['remove']

        # Extra fields
        extra:
            custom_field: [] # Array of Transformer aliases
```

### Mapping

Field name could contain dot to dive into array.

For example, if 'extra' contains the array :

```php
'user' => [
    'id' => /* ... */,
    'name' => [
        'first' => /* ... */,
        'last' => /* ... */,
    ],
]
```

It is possible to modify `ip` and `name` fields  :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    mapping:
        extra:
            user.ip: ['ip']
            user.name.first: ['hash']
            user.name.last: ['remove']
```

Warning, it is better to use field name without dot for performance.
Internally, when a field name contains a dot the PropertyAccessor is used instead of a simple array key access.

## Transformer

Currently, this bundle provides these transformers :
 * **ip**: Anonymize IP v4 or v6 (cf: `Symfony\Component\HttpFoundation\IpUtils::anonymize`)
 * **hash**: Encode the value using [hash](https://www.php.net/manual/fr/function.hash.php) function
 * **string**: Cast a `scalar` into `string` or call `__toString` on object
 * **remove**: Remove value (replaced by `--obfuscated--`)
 * **encrypt**: Encrypt the value (available only if encryptor is configured, cf: [Encrypt transformer](#encrypt-transformer))

### Chain transformers

You can chain transformers, for example to encode a "Stringable" object :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    # [...]
    mapping:
        context:
            custom_field: ['string', 'hash']
```

### Hash transformer

Encoder configuration :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    # [...]
    encoder:
        algo: 'sha256' # cf: https://www.php.net/manual/fr/function.hash-algos.php
        salt: '%env(SHADOW_LOGGER_ENCODER_SALT)%'
        binary: false
```

### Encrypt transformer

The bundle does not provide an encryption class.  
To use the "encrypt" transformer, you need to manually configure the encryptor.

First, you need to create an Adapter class and extends [EncryptorInterface](src/Encryptor/EncryptorInterface.php) :

```php
// src/Encryptor/EncryptorAdapter.php
namespace App\Encryptor;

use Aubes\ShadowLoggerBundle\Encryptor\EncryptorInterface;

class EncryptorAdapter implements EncryptorInterface
{
    // [...]

    public function encrypt(string $data, string $iv): string
    {
        // [...]

        return $encryptedValue;
    }

    public function generateIv(): string
    {
        // [...]

        return $iv;
    }
}
```

Next, register your class as a service (if service discovery is not used):

```yaml
# config/services.yaml
services:
    App\Encryptor\EncryptorAdapter: ~
```

Finally, configure your service Id in the ShadowLoggerBundle :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    # [...]
    encryptor: 'App\Encryptor\EncryptorAdapter'
```

This transformer replaces the value with an array :

```php
[
    'iv' => , // Random IV used to encrypt the value
    'value' => , // Encrypted value
]
```

## Custom transformer

First you need to create a Transformer class and extends [TransformerInterface](src/Transformer/TransformerInterface.php) :

```php
// src/Transformer/CustomTransformer.php
namespace App\Transformer;

class CustomTransformer implements TransformerInterface
{
    public function transform($data)
    {
        // [...]
    
        return $value;
    }
}
```

Next, register your class as a service with 'shadow_logger.transformer' tag :

```yaml
# config/services.yaml
services:
    App\Transformer\CustomTransformer:
        tags:
            - { name: 'shadow_logger.transformer', alias: 'custom' }
```
