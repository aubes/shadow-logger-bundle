<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle;

use Aubes\ShadowLoggerBundle\Encoder\Encoder;
use Aubes\ShadowLoggerBundle\Encryptor\DefaultEncryptor;
use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\LogRecordShadowProcessor;
use Aubes\ShadowLoggerBundle\Transformer\EncryptTransformer;
use Aubes\ShadowLoggerBundle\Transformer\TruncateTransformer;
use Aubes\ShadowLoggerBundle\Truncator\Truncator;
use Aubes\ShadowLoggerBundle\Visitor\ArrayKeyVisitor;
use Aubes\ShadowLoggerBundle\Visitor\PropertyAccessorVisitor;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
final class ShadowLoggerBundle extends AbstractBundle
{
    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress MixedMethodCall
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('debug')->defaultFalse()->info('Debug mode: add debug information when an exception occurred')->end()
                ->booleanNode('strict')->defaultTrue()->info('Strict mode: remove value when an exception occurred')->end()
                ->arrayNode('encoder')
                    ->children()
                        ->scalarNode('algo')->defaultValue('sha256')->end()
                        ->scalarNode('salt')->defaultValue('')->end()
                        ->booleanNode('binary')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('encryptor')
                    ->info('Use a service ID (string) or configure the built-in encryptor (key/cipher)')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static fn (string $v) => ['service' => $v])
                    ->end()
                    ->validate()
                        ->ifTrue(static fn (array $v) => $v['service'] !== null && $v['key'] !== null)
                        ->thenInvalid('You cannot specify both "service" and "key" for the encryptor.')
                    ->end()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('key')->defaultNull()->end()
                        ->scalarNode('cipher')->defaultValue('aes-256-cbc')->end()
                    ->end()
                ->end()
                ->arrayNode('truncators')
                    ->info('Named truncator variants, each available as a "truncate_{name}" transformer alias ("default" → "truncate")')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('keep_start')->defaultValue(2)->min(0)->end()
                            ->integerNode('keep_end')->defaultValue(2)->min(0)->end()
                            ->scalarNode('mask')->defaultValue('***')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('channels')
                    ->info('Logging channel list the ShadowProcessor should be pushed to')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('handlers')
                    ->info('Logging handler list the ShadowProcessor should be pushed to')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('mapping')
                    ->children()
                        ->arrayNode('extra')
                            ->arrayPrototype()
                                ->info('Field list in "extra"')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                        ->arrayNode('context')
                            ->arrayPrototype()
                                ->info('Field list in "context"')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $this->loadEncoder($config, $builder);
        $this->loadEncryptor($config, $builder);
        $this->loadTruncators($config, $builder);
        $this->loadProcessor($config, $builder);
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
    private function loadProcessor(array $config, ContainerBuilder $builder): void
    {
        if (!empty($config['channels']) && !empty($config['handlers'])) {
            throw new \InvalidArgumentException('You cannot specify both the "handler" and "channel"');
        }

        $processor = $builder->getDefinition(LogRecordShadowProcessor::class);

        foreach ($config['channels'] as $channel) {
            $processor->addTag('monolog.processor', ['channel' => $channel]);
        }

        foreach ($config['handlers'] as $handler) {
            $processor->addTag('monolog.processor', ['handler' => $handler]);
        }

        $processor->setArgument('$debug', $config['debug']);

        $this->loadTransformers($config, $builder, $processor);
    }

    /**
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArgument
     */
    private function loadEncryptor(array $config, ContainerBuilder $builder): void
    {
        if (!isset($config['encryptor']) || ($config['encryptor']['service'] === null && $config['encryptor']['key'] === null)) {
            $builder->removeDefinition(EncryptTransformer::class);
            $builder->removeDefinition(DefaultEncryptor::class);

            return;
        }

        $encryptTransformer = $builder->getDefinition(EncryptTransformer::class);

        if ($config['encryptor']['service'] !== null) {
            $encryptTransformer->setArgument('$encryptor', new Reference($config['encryptor']['service']));

            return;
        }

        $builder->getDefinition(DefaultEncryptor::class)
            ->setArgument('$key', $config['encryptor']['key'])
            ->setArgument('$cipher', $config['encryptor']['cipher']);

        $encryptorRef = new Reference(DefaultEncryptor::class);

        $encryptTransformer->setArgument('$encryptor', $encryptorRef);
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayOffset
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArgument
     */
    private function loadTransformers(array $config, ContainerBuilder $builder, Definition $processor): void
    {
        $transformersListTag = $builder->findTaggedServiceIds('shadow_logger.transformer');

        $transformerIdList = [];
        foreach ($transformersListTag as $transformerId => $transformerTag) {
            foreach ($transformerTag as $transformerTagParams) {
                if (empty($transformerTagParams['alias'])) {
                    throw new \InvalidArgumentException('Alias is required on "shadow_logger.transformer" tag');
                }

                $transformerIdList[$transformerTagParams['alias']] = $transformerId;
            }
        }

        foreach ($config['mapping'] as $property => $propertyConfig) {
            foreach ($propertyConfig as $field => $transformersAlias) {
                $transformers = [];

                foreach ($transformersAlias as $alias) {
                    if (!isset($transformerIdList[$alias])) {
                        throw new \InvalidArgumentException('Unknown transformer alias');
                    }

                    $transformers[] = new Reference($transformerIdList[$alias]);
                }

                $visitor = $builder->getDefinition(ArrayKeyVisitor::class);
                if (\str_contains($field, '.')) {
                    $visitor = $builder->getDefinition(PropertyAccessorVisitor::class);
                    $field = $this->propertyAccessorArray($field);
                }

                $dataTransformer = new Definition(DataTransformer::class, [
                    $field,
                    $visitor,
                    $transformers,
                    $config['strict'],
                ]);

                $processor->addMethodCall('addDataTransformer', [$property, $dataTransformer]);
            }
        }
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedOperand
     * @psalm-suppress MixedArrayAccess
     */
    private function loadTruncators(array $config, ContainerBuilder $builder): void
    {
        foreach ($config['truncators'] as $name => $truncatorConfig) {
            $alias = $name === 'default' ? 'truncate' : 'truncate_' . $name;

            $truncatorId = 'shadow_logger.truncator.' . $name;
            $transformerId = 'shadow_logger.truncate_transformer.' . $name;

            $builder->register($truncatorId, Truncator::class)
                ->setArguments([
                    $truncatorConfig['keep_start'],
                    $truncatorConfig['keep_end'],
                    $truncatorConfig['mask'],
                ]);

            $builder->register($transformerId, TruncateTransformer::class)
                ->setArgument('$truncator', new Reference($truncatorId))
                ->addTag('shadow_logger.transformer', ['alias' => $alias]);
        }
    }

    /** @psalm-suppress MixedArrayAccess */
    private function loadEncoder(array $config, ContainerBuilder $builder): void
    {
        if (empty($config['encoder'])) {
            return;
        }

        $encoder = $builder->getDefinition(Encoder::class);
        $encoder->setArgument('$algo', $config['encoder']['algo']);
        $encoder->setArgument('$salt', $config['encoder']['salt']);
        $encoder->setArgument('$binary', $config['encoder']['binary']);
    }

    private function propertyAccessorArray(string $path): string
    {
        return '[' . \implode('][', \explode('.', $path)) . ']';
    }
}
