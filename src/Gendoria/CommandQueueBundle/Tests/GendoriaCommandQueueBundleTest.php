<?php

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use Gendoria\CommandQueueBundle\GendoriaCommandQueueBundle;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests of GendoriaCommandQueueBundle
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class GendoriaCommandQueueBundleTest extends PHPUnit_Framework_TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new GendoriaCommandQueueBundle();
        $this->assertInstanceOf(GendoriaCommandQueueExtension::class, $bundle->getContainerExtension());
    }
    
    public function testBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->withConsecutive(
                array($this->isInstanceOf(CommandProcessorPass::class)),
                array($this->isInstanceOf(PoolsPass::class))
            );
        
        $bundle = new GendoriaCommandQueueBundle();
        $bundle->build($container);
    }
}
