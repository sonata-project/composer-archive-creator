<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_composer', 'array');

        $rootNode->children()
            ->scalarNode('git')->defaultValue(trim(`which git`))->end()
            ->scalarNode('composer')->defaultValue(trim(`which composer`))->end()
            ->arrayNode('reporting')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('mailer')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('enabled')->defaultValue(false)->end()
                            ->scalarNode('from')->defaultValue('no-reply@sonata-project.org')->end()
                            ->scalarNode('to')->defaultValue('contact@sonata-project.org')->end()
                            ->scalarNode('subject')->defaultValue('[archive-creator] %s')->end()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(25)->end()
                            ->scalarNode('username')->defaultValue(null)->end()
                            ->scalarNode('password')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}