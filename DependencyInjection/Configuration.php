<?php
namespace Bigyouth\BigyouthPageCacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Bigyouth\BigyouthPageCacheBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(){
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bigyouth_page_cache');

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->integerNode('ttl')->defaultValue(300)->end()
                ->scalarNode('type')->defaultValue('filesystem')->end()
                ->scalarNode('redis_host')->defaultValue('localhost')->end()
                ->scalarNode('redis_port')->defaultValue('6379')->end()
                ->arrayNode('exclude')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
