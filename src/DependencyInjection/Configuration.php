<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bowl_of_soup_normalizer');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('register_annotations')->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }
}
