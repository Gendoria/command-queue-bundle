<?php

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\RegisterSerializerDriversPass;
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
        $container = new ContainerBuilder();
        
        $bundle = new GendoriaCommandQueueBundle();
        $bundle->build($container);
        $passes = $container->getCompilerPassConfig()->getPasses();
        $hasPoolsPass = false;
        $hasCommandProcessorPass = false;
        $hasSerializersPass = false;
        foreach ($passes as $pass) {
            if ($pass instanceof CommandProcessorPass) {
                $hasCommandProcessorPass = true;
            } elseif ($pass instanceof PoolsPass) {
                $hasPoolsPass = true;
            } elseif ($pass instanceof RegisterSerializerDriversPass) {
                $hasSerializersPass = true;
            }
        }
        $this->assertTrue($hasCommandProcessorPass, "Command processors pass should have been registered.");
        $this->assertTrue($hasPoolsPass, "Pools pass should have been registered.");
        $this->assertTrue($hasSerializersPass, "Serializers pass should have been registered.");
    }
}
