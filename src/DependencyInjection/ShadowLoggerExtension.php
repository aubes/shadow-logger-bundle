<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\DependencyInjection;

use Aubes\ShadowLoggerBundle\Encoder\Encoder;
use Aubes\ShadowLoggerBundle\Logger\DataTransformer;
use Aubes\ShadowLoggerBundle\Logger\LogRecordShadowProcessor;
use Aubes\ShadowLoggerBundle\Logger\ShadowProcessor;
use Aubes\ShadowLoggerBundle\Transformer\EncryptTransformer;
use Aubes\ShadowLoggerBundle\Visitor\ArrayKeyVisitor;
use Aubes\ShadowLoggerBundle\Visitor\PropertyAccessorVisitor;
use Monolog\LogRecord;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class ShadowLoggerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $this->loadEncoder($config, $container);
        $this->loadEncryptor($config, $container);
        $this->loadProcessor($config, $container);
    }

    protected function loadProcessor(array $config, ContainerBuilder $container): void
    {
        if (!empty($config['channels']) && !empty($config['handlers'])) {
            throw new \InvalidArgumentException('You cannot specify both the "handler" and "channel"');
        }

        $processor = $container->getDefinition(ShadowProcessor::class);

        // Compatibility with Monolog >= 3.0
        if (\class_exists(LogRecord::class) && \method_exists(LogRecord::class, 'with')) {
            $processor->setClass(LogRecordShadowProcessor::class);
        }

        foreach ($config['channels'] as $channel) {
            $processor->addTag('monolog.processor', ['channel' => $channel]);
        }

        foreach ($config['handlers'] as $handler) {
            $processor->addTag('monolog.processor', ['handler' => $handler]);
        }

        $processor->setArgument('$debug', $config['debug']);

        $this->loadTransformers($config, $container, $processor);
    }

    public function loadEncryptor(array $config, ContainerBuilder $container): void
    {
        if (!isset($config['encryptor'])) {
            $container->removeDefinition(EncryptTransformer::class);

            return;
        }

        $encryptTransformer = $container->getDefinition(EncryptTransformer::class);
        $encryptTransformer->setArgument('$encryptor', new Reference($config['encryptor']));
    }

    /**
     * @SuppressWarnings(PMD.ElseExpression)
     */
    protected function loadTransformers(array $config, ContainerBuilder $container, Definition $processor): void
    {
        $transformersListTag = $container->findTaggedServiceIds('shadow_logger.transformer');

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

                if (\str_contains($field, '.')) {
                    $visitor = $container->getDefinition(PropertyAccessorVisitor::class);

                    $field = $this->propertyAccessorArray($field);
                } else {
                    $visitor = $container->getDefinition(ArrayKeyVisitor::class);
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

    protected function loadEncoder(array $config, ContainerBuilder $container): void
    {
        if (empty($config['encoder'])) {
            return;
        }

        $encoder = $container->getDefinition(Encoder::class);
        $encoder->setArgument('$algo', $config['algo']);
        $encoder->setArgument('$salt', $config['salt']);
        $encoder->setArgument('$binary', $config['binary']);
    }

    protected function propertyAccessorArray(string $path): string
    {
        $arrayPath = '';
        foreach (\explode('.', $path) as $part) {
            $arrayPath .= "[{$part}]";
        }

        return $arrayPath;
    }
}
