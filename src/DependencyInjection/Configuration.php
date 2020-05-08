<?php

namespace BowlOfSoup\NormalizerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bowl_of_soup_normalizer');

        if (is_callable([$treeBuilder, 'getRootNode'])) {
            $treeBuilder
                ->getRootNode()
                ->children()
                ->booleanNode('register_annotations')->defaultValue(false)->end()
                ->end();
        }
        if (is_callable([$treeBuilder, 'root'])) {
            $treeBuilder
                ->root('bowl_of_soup_normalizer')
                ->children()
                ->booleanNode('register_annotations')->defaultValue(false)->end()
                ->end();
        }

        return $treeBuilder;
    }
}
