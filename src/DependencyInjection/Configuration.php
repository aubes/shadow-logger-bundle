<?php

declare(strict_types=1);

namespace Aubes\ShadowLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('shadow_logger');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('debug')->defaultFalse()->info('Debug mode: add debug information when an exception occurred')->end()
                ->scalarNode('strict')->defaultTrue()->info('Strict mode: remove value when an exception occurred')->end()
                ->arrayNode('encoder')
                    ->children()
                        ->scalarNode('algo')->defaultValue('sha256')->end()
                        ->scalarNode('salt')->defaultValue('')->end()
                        ->booleanNode('binary')->defaultFalse()->end()
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
