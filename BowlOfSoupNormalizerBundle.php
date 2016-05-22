<?php

namespace BowlOfSoup\NormalizerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BowlOfSoupNormalizerBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

//        $container->addCompilerPass(new LoadExtractorParsersPass());
    }
}
