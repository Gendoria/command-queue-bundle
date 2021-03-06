<?php

/**
 * All rights reserved
 */

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection;

use Gendoria\CommandQueue\Console\Command\ListWorkersCommand;
use Gendoria\CommandQueue\Console\Command\RunWorkerCommand;
use Gendoria\CommandQueue\ProcessorFactory\ProcessorFactoryInterface;
use Gendoria\CommandQueue\QueueManager\MultipleQueueManager;
use Gendoria\CommandQueue\QueueManager\NullQueueManager;
use Gendoria\CommandQueue\SendDriver\SendDriverInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use Gendoria\CommandQueueBundle\Serializer\SymfonySerializer;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerManager;
use InvalidArgumentException;
use JMS\Serializer\Serializer as JmsSerializerClass;
use PHPUnit_Framework_TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionObject;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializerClass;

/**
 * Description of ManagerTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 * @group CommandQueue
 * @group legacy
 */
class DependencyInjectionTest extends PHPUnit_Framework_TestCase
{
    public function testNotEnabled()
    {
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'serializer' => new Definition(SymfonySerializerClass::class),
            'jms_serializer' => new Definition(JmsSerializerClass::class),
        ));
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
            'enabled' => false,
        );
        $extension->load(array($config), $container);
        
        $container->set('logger', new NullLogger());
        $container->set('doctrine', $this->getMockBuilder(EntityManagerInterface::class)->getMock());
        $container->set('gendoria_command_queue.serializer.symfony', $this->getMockBuilder(SymfonySerializer::class)->disableOriginalConstructor()->getMock());
        $container->set('event_dispatcher', $this->getMockBuilder(EventDispatcherInterface::class)->getMock());
        $container->set('gendoria_command_queue.processor_factory', $this->getMockBuilder(ProcessorFactoryInterface::class)->geTMock());
        
        $container->compile();

        /* @var $manager MultipleQueueManager */
        $manager = $container->get('gendoria_command_queue.manager');
        $this->assertInstanceOf(NullQueueManager::class, $manager);
    }
    
    public function testHasDefaultPool()
    {
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'serializer' => new Definition(SymfonySerializerClass::class),
            'jms_serializer' => new Definition(JmsSerializerClass::class),
        ));
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
            'routes' => array(
                'Command' => 'default'
            )
        );
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
        ));
        $extension->load(array($config), $container);
        
        $container->set('logger', new NullLogger());
        $container->set('doctrine', $this->getMockBuilder(EntityManagerInterface::class)->getMock());
        $container->set('gendoria_command_queue.serializer.symfony', $this->getMockBuilder(SymfonySerializer::class)->disableOriginalConstructor()->getMock());
        $container->set('event_dispatcher', $this->getMockBuilder(EventDispatcherInterface::class)->getMock());
        $container->set('gendoria_command_queue.processor_factory', $this->getMockBuilder(ProcessorFactoryInterface::class)->geTMock());
                
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();

        /* @var $manager MultipleQueueManager */
        $manager = $container->get('gendoria_command_queue.manager');
        $this->assertInstanceOf(MultipleQueueManager::class, $manager);
        $reflectionClass = new ReflectionObject($manager);
        $defaultPoolProp = $reflectionClass->getProperty('defaultPool');
        $defaultPoolProp->setAccessible(true);
        $this->assertNotEmpty($defaultPoolProp->getValue($manager));
        $sendDriversProp = $reflectionClass->getProperty('sendDrivers');
        $sendDriversProp->setAccessible(true);
        $this->assertEquals($sendDriversProp->getValue($manager), array('default' => $sendDriverMock));
    }
    
    public function testNoClearLogsListener()
    {
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
            'listeners' => array(
                'clear_logs' => false,
            )
        );
        $extension->load(array($config), $container);
        $this->assertFalse($container->hasDefinition('gendoria_command_queue.listener.clear_logs'));
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.listener.clear_entity_managers'));
    }    
    
    public function testNoClearEntityManagersListener()
    {
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
            'listeners' => array(
                'clear_entity_managers' => false,
            )
        );
        $extension->load(array($config), $container);
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.listener.clear_logs'));
        $this->assertFalse($container->hasDefinition('gendoria_command_queue.listener.clear_entity_managers'));
    }    
    
    public function testRegisteredCommands()
    {
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'serializer' => new Definition(SymfonySerializerClass::class),
            'jms_serializer' => new Definition(JmsSerializerClass::class),
        ));
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
        ));
        $extension->load(array($config), $container);
        
        $container->set('logger', new NullLogger());
        $container->set('doctrine', $this->getMockBuilder(EntityManagerInterface::class)->getMock());
        $container->set('gendoria_command_queue.serializer.symfony', $this->getMockBuilder(SymfonySerializer::class)->disableOriginalConstructor()->getMock());
        $container->set('event_dispatcher', $this->getMockBuilder(EventDispatcherInterface::class)->getMock());
        $container->set('gendoria_command_queue.processor_factory', $this->getMockBuilder(ProcessorFactoryInterface::class)->geTMock());
                
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();
        
        $listDef = $container->getDefinition('gendoria_command_queue.command.list');
        $this->assertTrue($listDef->hasTag('console.command'), 'List not registered as command');
        $listCommand = $container->get('gendoria_command_queue.command.list');
        $this->assertInstanceOf(ListWorkersCommand::class, $listCommand);
        $listRefl = new ReflectionClass($listCommand);
        $listCommandManagerProp = $listRefl->getProperty('runnerManager');
        $listCommandManagerProp->setAccessible(true);
        $this->assertInstanceOf(WorkerRunnerManager::class, $listCommandManagerProp->getValue($listCommand));
        
        $runDef = $container->getDefinition('gendoria_command_queue.command.run');
        $this->assertTrue($runDef->hasTag('console.command'), 'Run not registered as command');
        $runCommand = $container->get('gendoria_command_queue.command.run');
        $this->assertInstanceOf(RunWorkerCommand::class, $runCommand);
        $runRefl = new ReflectionClass($runCommand);
        $runCommandManagerProp = $runRefl->getProperty('runnerManager');
        $runCommandManagerProp->setAccessible(true);
        $this->assertInstanceOf(WorkerRunnerManager::class, $runCommandManagerProp->getValue($runCommand));
    }    
    
    public function testNonUniqueSendServices()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Each pool has to have unique send service - duplicate service id "dummy" found.');
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
                'default1' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
        ));
        $extension->load(array($config), $container);
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();
    }
    
    public function testIncorrectTaggedServices()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Service "incorrect" does not implement one of required interfaces.');
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $incorrectQueueManagerMock = $this->getMockBuilder(stdClass::class)->getMock();
        $incorrectDefinition = new Definition(get_class($incorrectQueueManagerMock));
        $incorrectDefinition->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
            'incorrect' => $incorrectDefinition,
        ));
        $extension->load(array($config), $container);
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();
    }    
    
    public function testIncorrectRoutes()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Pool "nonExistingPool" required in command routing is not present.');
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
            'routes' => array(
                'dummyClass' => 'nonExistingPool',
            ),
        );
        $extension->load(array($config), $container);
        $container->compile();
    }
    
    public function testNoPools()
    {
        $this->setExpectedException(InvalidConfigurationException::class, 'The child node "pools" at path "gendoria_command_queue" must be configured.');
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
        );
        $extension->load(array($config), $container);
    }
    
    public function testPrependNoDoctrine()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $container->prependExtensionConfig('gendoria_command_queue', $config);
        
        $extension = new GendoriaCommandQueueExtension();
        $extension->prepend($container);
        $parsedConfig = $container->getExtensionConfig($extension->getAlias());
        $extension->load($parsedConfig, $container);
        $this->assertFalse($container->has('gendoria_command_queue.listener.clear_entity_managers'));
        $this->assertTrue($container->has('gendoria_command_queue.listener.clear_logs'));
    }    
    
    public function testPrependNoBundles()
    {
        $container = new ContainerBuilder();
        
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $container->prependExtensionConfig('gendoria_command_queue', $config);
        
        $extension = new GendoriaCommandQueueExtension();
        $extension->prepend($container);
        $parsedConfig = $container->getExtensionConfig($extension->getAlias());
        $extension->load($parsedConfig, $container);
        $this->assertTrue($container->has('gendoria_command_queue.listener.clear_entity_managers'));
    }    
    
}
