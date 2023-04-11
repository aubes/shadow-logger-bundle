# Shadow Logger Bundle

This Symfony bundle provide a monolog processor to transform log data, in order to respect GDPR or to anonymize sensitive data.

It allows Ip anonymization, encoding or removing data in the log.

## Installation

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
    # Logging channels the ShadowProcessor should be pushed to
    handlers: ['app']
    
    # Logging handlers the ShadowProcessor should be pushed to
    #channels: ['app']
    
    mapping:
        # Extra fields
        extra:
            custom_field: [] # Transformer aliases

        # Context fields
        context:
            custom_field: [] # Transformer aliases
```

### Mapping

Field name could contain dot to dive into array.

For example, if 'extra' contain the array :

```php
[
    'user' => [
        'id' => /* ... */,
        'name' => /* ... */,
    ]
]
```

It is possible to modify `ip` and `name` fields  :

```yaml
# config/packages/shadow-logger.yaml
shadow_logger:
    mapping:
        extra:
            user.ip: ['ip']
            user.name: ['hash']
```

## Transformer

Actually, this bundle provides those transformers :
 * **ip**: Anonymize IP v4 or v6 (cf: `Symfony\Component\HttpFoundation\IpUtils::anonymize`)
 * **hash**: Encode the value
 * **string**: Cast a `scalar` into `string` or call `__toString` on object
 * **remove**: Remove value

### Chain transformers

You can chain transformers, for example to encode a "Stringable" object :

```yaml
shadow_logger:
    mapping:
        context:
            custom_field: ['string', 'hash']
```

## Add a custom transformer

First you need to create a Transformer class and extends [TransformerInterface](src/Transformer/TransformerInterface.php) :

```php
<?php

namespace App\Transformer;

class CustomTransformer implements TransformerInterface {
    public function transform($data) {
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
