<?php

/**
 * All rights reserved
 */

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueue\QueueManager\MultipleQueueManager;
use Gendoria\CommandQueue\SendDriver\SendDriverInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\GendoriaCommandQueueExtension;
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
