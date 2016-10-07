<?php

/**
 * All rights reserved
 */

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueue\QueueManager\MultipleQueueManager;
use Gendoria\CommandQueue\QueueManager\MultipleQueueManagerInterface;
use Gendoria\CommandQueue\QueueManager\NullQueueManager;
use Gendoria\CommandQueue\QueueManager\QueueManagerInterface;
use Gendoria\CommandQueue\SendDriver\SendDriverInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\CommandProcessorPass;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ReflectionObject;
use stdClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
            'enable' => false,
        );
        $extension->load(array($config), $container);
        $container->compile();

        /* @var $manager MultipleQueueManager */
        $manager = $container->get('gendoria_command_queue.manager');
        $this->assertInstanceOf(NullQueueManager::class, $manager);
    }
    
    public function testHasDefaultPool()
    {
        $container = new ContainerBuilder();
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
    
    /**
     * @dataProvider getSingleQueueManagerDefinition
     */
    public function testSingleQueueManager(Definition $definition, $poolName, $poolValid = true, $hasValidTags = true)
    {
        if (!$poolValid) {
            $this->setExpectedException(InvalidArgumentException::class, sprintf(
                'Service "%s" requests non existing queue pool "%s".',
                'single',
                $definition->getTag(PoolsPass::QUEUE_MANAGER_TAG)[0]['pool']
            ));
        }
        if (!$hasValidTags) {
            $this->setExpectedException(InvalidArgumentException::class, 'Only single ' . PoolsPass::QUEUE_MANAGER_TAG . ' tag possible on service single.');
        }
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        if ($poolName !== 'default' && $poolValid) {
            $config['pools'][$poolName] = array(
                'send_driver' => '@dummy1',
            );
            $container->addDefinitions(array(
                'dummy1' => new Definition(get_class($sendDriverMock)),
            ));
        }
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
            'single' => $definition,
        ));
        $extension->load(array($config), $container);
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();
    }
    
    /**
     * Get definitions for various queue manager passes
     */
    public function getSingleQueueManagerDefinition()
    {
        $singleQueueManager = $this->getMockBuilder(QueueManagerInterface::class)->getMock();
        $definitionDefaultPool = new Definition(get_class($singleQueueManager));
        $definitionDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool = new Definition(get_class($singleQueueManager));
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG, array('pool' => 'newpool'));
        $definitionInvalidTags = new Definition(get_class($singleQueueManager));
        $definitionInvalidTags->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionInvalidTags->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        return array(
            array($definitionDefaultPool, 'default'),
            array($definitionNonDefaultPool, 'newpool'),
            array($definitionNonDefaultPool, 'newpool', false),
            array($definitionInvalidTags, 'default', true, false)
        );
    }
    
    /**
     * @dataProvider getMultipleQueueManagerDefinition
     */
    public function testMultipleQueueManager(Definition $definition, $poolsValid = true)
    {
        $tags = $definition->getTag(PoolsPass::QUEUE_MANAGER_TAG);
        if (!$poolsValid) {
            $invalidPool = '';
            foreach ($tags as $tag) {
                if (!empty($tag['pool']) && $tag['pool'] !== 'default') {
                    $invalidPool = $tag['pool'];
                    break;
                }
            }
            $this->setExpectedException(InvalidArgumentException::class, sprintf(
                'Service "%s" requests non existing queue pool "%s".',
                'single',
                $invalidPool
            ));
        }
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy_default',
                )
            ),
        );
        $container->addDefinitions(array(
            'dummy_default' => new Definition(get_class($sendDriverMock)),
        ));
        if ($poolsValid) {
            foreach ($tags as $tag) {
                $poolName = !empty($tag['pool']) ? $tag['pool'] : 'default';
                if ($poolName == 'default') {
                    continue;
                }
                $config['pools'][$poolName] = array(
                    'send_driver' => '@dummy_'.$poolName,
                );
                $container->addDefinitions(array(
                    'dummy_'.$poolName => new Definition(get_class($sendDriverMock)),
                ));
            }
        }
        $container->addDefinitions(array(
            'single' => $definition,
        ));
        $extension->load(array($config), $container);
        $container->addCompilerPass(new PoolsPass());
        $container->addCompilerPass(new CommandProcessorPass());
        $container->compile();
    }    
    
    /**
     * Get definitions for various queue manager passes
     */
    public function getMultipleQueueManagerDefinition()
    {
        $multipleQueueManager = $this->getMockBuilder(MultipleQueueManagerInterface::class)->getMock();
        $definitionDefaultPool = new Definition(get_class($multipleQueueManager));
        $definitionDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool = new Definition(get_class($multipleQueueManager));
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG, array('pool' => 'newpool'));
        return array(
            array($definitionDefaultPool),
            array($definitionNonDefaultPool),
            array($definitionNonDefaultPool, false),
        );
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
    
    public function testInvalidSendDriverNoService()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Non existing send driver service provided: @dummy.');
        $container = new ContainerBuilder();
        $extension = new GendoriaCommandQueueExtension();
        $config = array(
            'pools' => array(
                'default' => array(
                    'send_driver' => '@dummy',
                ),
            ),
        );
        $extension->load(array($config), $container);
        $container->addCompilerPass(new PoolsPass());
        $container->compile();
    }
    
    public function testInvalidSendDriverInvalidInterface()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Service "dummy" does not implement interface "'.SendDriverInterface::class.'".');
        $container = new ContainerBuilder();
        $sendDriverMock = $this->getMockBuilder(stdClass::class)->getMock();
        $extension = new GendoriaCommandQueueExtension();
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
        $container->addCompilerPass(new PoolsPass());
        $container->compile();
    }
}
