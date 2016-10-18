<?php

namespace Gendoria\CommandQueueBundle\Tests\Worker;

use Gendoria\CommandQueue\Worker\WorkerInterface;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerInterface;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerManager;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of WorkerRunnerManagerTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnerManagerTest extends PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $container = new ContainerBuilder();
        $service = $this->getMockBuilder(WorkerInterface::class)->getMock();
        $container->set('test', $service);
        $manager = new WorkerRunnerManager($container);
        $this->assertFalse($manager->has('test'));
        $manager->addRunner('test', 'test');
        $this->assertTrue($manager->has('test'));
    }
    
    public function testAddNoService()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Service container does not have required service registered.');
        $container = new ContainerBuilder();
        $manager = new WorkerRunnerManager($container);
        $manager->addRunner('test', 'test');
    }
    
    public function testRun()
    {
        $container = new ContainerBuilder();
        $service = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $service->expects($this->once())
            ->method('run');
        $container->set('test', $service);
        $manager = new WorkerRunnerManager($container);
        $manager->addRunner('test', 'test');
        $manager->run('test');
    }    
    
    public function testRunException()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'No runner service registered for provided name.');
        $container = new ContainerBuilder();
        $manager = new WorkerRunnerManager($container);
        $manager->run('test');
    }    
}
