<?php

namespace Gendoria\CommandQueueBundle;

use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\RegisterSerializerDriversPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Command queue bundle.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class GendoriaCommandQueueBundle extends Bundle
{
    /**
     * Get default extension class instance.
     *
     * @return GendoriaCommandQueueExtension
     */
    public function getContainerExtension()
    {
        if (null === $this->extension || false === $this->extension) {
            $this->extension = new GendoriaCommandQueueExtension();
        }

        return $this->extension;
    }

    /**
     * Build bundle.
     *
     * During build, two compile passes are registered: for fetching command processors and pool configurations.
     *
     * @param ContainerBuilder $container Container builder.
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CommandProcessorPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new PoolsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new RegisterSerializerDriversPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
