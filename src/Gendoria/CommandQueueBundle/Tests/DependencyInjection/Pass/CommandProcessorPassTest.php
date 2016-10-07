<?php

/**
 * All rights reserved
 */

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\ProcessorFactoryInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_Generator;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of ManagerTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 * @group CommandQueue
 * @group legacy
 */
class CommandProcessorPassTest extends PHPUnit_Framework_TestCase
{
    const FACTORY_ID = 'factory';
    const TAG_NAME = 'tag';

    public function testEmpty()
    {
        $container = new ContainerBuilder();
        $pass = new CommandProcessorPass();
        $pass->process($container);
    }

    /**
     * @dataProvider getCorrect
     */
    public function testCorrect(array $definitions, $expectedDispatcherCalls)
    {
        $container = new ContainerBuilder();
        $container->addDefinitions($definitions);
        /* @var $dispatcher PHPUnit_Framework_MockObject_MockObject|PHPUnit_Framework_MockObject_Generator|ProcessorFactoryInterface */
        $dispatcher = $container->getDefinition(self::FACTORY_ID);
        $dispatcher->expects($this->exactly($expectedDispatcherCalls))
            ->method('addMethodCall')
            ->with('registerProcessorIdForCommand');
        $pass = new CommandProcessorPass(self::FACTORY_ID, self::TAG_NAME);
        $pass->process($container);
    }

    public function getCorrect()
    {
        $processor = $this->getMockBuilder(CommandProcessorInterface::class)->getMock();
        
        $singleCommandProcessor = new Definition(get_class($processor));
        $singleCommandProcessor->addTag(self::TAG_NAME, array('command' => 'command1'));
        
        $multipleCommandsProcessor = new Definition(get_class($processor));
        $multipleCommandsProcessor->addTag(self::TAG_NAME, array('command' => 'command1'));
        $multipleCommandsProcessor->addTag(self::TAG_NAME, array('command' => 'command2'));
        
        return array(
            array(
                array(
                    self::FACTORY_ID => $this->getMockBuilder(Definition::class)->getMock(),
                    'service1' => $singleCommandProcessor,
                ),
                1
            ),
            array(
                array(
                    self::FACTORY_ID => $this->getMockBuilder(Definition::class)->getMock(),
                    'service1' => $multipleCommandsProcessor,
                ),
                2
            ),
            array(
                array(
                    self::FACTORY_ID => $this->getMockBuilder(Definition::class)->getMock(),
                    'service1' => $singleCommandProcessor,
                    'service2' => $multipleCommandsProcessor,
                ),
                3
            ),
        );
    }
    
    /**
     * @dataProvider getIncorrect
     */
    public function testIncorrect(array $definitions, $exceptionClass, $exceptionMessage = '')
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);
        $container = new ContainerBuilder();
        $container->addDefinitions($definitions);
        $pass = new CommandProcessorPass(self::FACTORY_ID, self::TAG_NAME);
        $pass->process($container);
    }
    
    public function getIncorrect()
    {
        $processorFactory = $this->getMockBuilder(ProcessorFactoryInterface::class)->getMock();
        $processor = $this->getMockBuilder(CommandProcessorInterface::class)->getMock();
        
        $nonPublicDefinition = new Definition(get_class($processor));
        $nonPublicDefinition->setPublic(false);
        $nonPublicDefinition->addTag(self::TAG_NAME);
        
        $abstractDefinition = new Definition(get_class($processor));
        $abstractDefinition->setAbstract(true);
        $abstractDefinition->addTag(self::TAG_NAME);
        
        $noCommandDefinition = new Definition(get_class($processor));
        $noCommandDefinition->addTag(self::TAG_NAME);        
        
        $noInterfaceDefinition = new Definition(stdClass::class);
        $noInterfaceDefinition->addTag(self::TAG_NAME);        
        
        return array(
            array(
                array(
                    self::FACTORY_ID => new Definition(get_class($processorFactory)),
                    'service1' => $nonPublicDefinition
                ),
                InvalidArgumentException::class,
                'The service "service1" must be public as services are lazy-loaded.'
            ),
            array(
                array(
                    self::FACTORY_ID => new Definition(get_class($processorFactory)),
                    'service1' => $abstractDefinition
                ),
                InvalidArgumentException::class,
                'The service "service1" must not be abstract as services are lazy-loaded.'
            ),
            array(
                array(
                    self::FACTORY_ID => new Definition(get_class($processorFactory)),
                    'service1' => $noInterfaceDefinition
                ),
                InvalidArgumentException::class,
                'The service "service1" has to implement '.CommandProcessorInterface::class.'.'
            ),            
            array(
                array(
                    self::FACTORY_ID => new Definition(get_class($processorFactory)),
                    'service1' => $noCommandDefinition
                ),
                InvalidArgumentException::class,
                'The service "service1" is tagged as processor without specifying "command" attribute'
            ),
        );
    }

}
