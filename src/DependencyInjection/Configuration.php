<?php

namespace KleytonSantos\Pipeline\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pipeline');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('pipelines')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->scalarPrototype()->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
