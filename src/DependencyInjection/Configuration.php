<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shadow_logger');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
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
                        ->ifTrue(static fn ($v) => $v['service'] !== null && $v['key'] !== null)
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

        return $treeBuilder;
    }
}
