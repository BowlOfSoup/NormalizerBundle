<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BowlOfSoupNormalizerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $parameters = $this->processConfiguration($configuration, $configs);

        $container->setParameter('bowl_of_soup_normalizer.register_annotations', $parameters['register_annotations']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');
    }
}
