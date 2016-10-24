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
        $container->set('test_svc', $service);
        $manager = new WorkerRunnerManager($container);
        $this->assertFalse($manager->has('test'));
        $manager->addRunnerService('test', 'test_svc');
        $this->assertTrue($manager->has('test'));
        $this->assertEquals(array('test'), $manager->getRunners());
    }
    
    public function testAddNoService()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Service container does not have required service registered.');
        $container = new ContainerBuilder();
        $manager = new WorkerRunnerManager($container);
        $manager->addRunnerService('test', 'test');
    }
    
    public function testRun()
    {
        $container = new ContainerBuilder();
        $service = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $service->expects($this->once())
            ->method('run');
        $container->set('test_svc', $service);
        $container->compile();
        $manager = new WorkerRunnerManager($container);
        $manager->addRunnerService('test', 'test_svc');
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
