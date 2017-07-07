<?php

namespace Sima\Console\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationDefinitions implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $configNode = $treeBuilder->root('sima');

        $configNode->children()
            ->arrayNode('database')->children()
                ->enumNode('driver')->values(['mysql'])->end()
                ->scalarNode('host')->end()
                ->scalarNode('port')->end()
                ->scalarNode('database')->end()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
                ->scalarNode('charset')->defaultValue('utf8')->end()
                ->scalarNode('collation')->defaultValue('utf8_unicode_ci')->end()
                ->scalarNode('prefix')->defaultValue('')->end()
            ->end()
        ->end();

        $configNode->children()
            ->arrayNode('avtotal')->children()
                ->scalarNode('apihost')->end()
                ->scalarNode('apikey')->end()
            ->end()
        ->end();

        $configNode->children()
            ->arrayNode('mailer')->children()
                ->scalarNode('transport')->end()
                ->scalarNode('host')->end()
                ->scalarNode('user')->end()
                ->scalarNode('password')->end()
            ->end()
        ->end();

        $configNode->children()
            ->arrayNode('scan')->children()
                ->variableNode('extensions')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
