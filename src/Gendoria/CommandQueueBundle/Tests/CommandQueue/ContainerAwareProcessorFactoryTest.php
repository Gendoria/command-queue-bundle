<?php

namespace Gendoria\CommandQueueBundle\Tests\CommandQueue;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueueBundle\CommandQueue\ContainerAwareProcessorFactory;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummyCommandProcessor;
use PHPUnit_Framework_TestCase;
use ReflectionObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of ContainerAwareProcessorFactory
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class ContainerAwareProcessorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterProcessorIdforCommand()
    {
        $container = new ContainerBuilder();
        $container->compile();
        $factory = new ContainerAwareProcessorFactory($container);
        $factory->registerProcessorIdForCommand('Command', 'service');
        $refl = new ReflectionObject($factory);
        $idsProp = $refl->getProperty('serviceIds');
        $idsProp->setAccessible(true);
        $this->assertArrayHasKey('Command', $idsProp->getValue($factory));
        $this->assertTrue($factory->hasProcessor('Command'));
    }
    
    public function testHasProcessor()
    {
        $container = new ContainerBuilder();
        $container->compile();
        $factory = new ContainerAwareProcessorFactory($container);
        $this->assertFalse($factory->hasProcessor('Command'));
    }
    
    public function testGetProcessor()
    {
        $command = $this->getMockBuilder(CommandInterface::class)->getMock();
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'service' => new Definition(DummyCommandProcessor::class),
        ));
        
        $container->compile();
        $factory = new ContainerAwareProcessorFactory($container);
        $factory->registerProcessorIdForCommand(get_class($command), 'service');
        $this->assertTrue($factory->hasProcessor(get_class($command)));
        $this->assertInstanceOf(DummyCommandProcessor::class, $factory->getProcessor($command));
    }
}
