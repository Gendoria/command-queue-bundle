<?php

/**
 * All rights reserved
 */

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueueBundle\Event\QueueWorkerRunEvent;
use PHPUnit_Framework_TestCase;

/**
 * Description of ManagerTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 * @group CommandQueue
 * @group legacy
 */
class QueueWorkerRunEventTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $worker = $this->getMockBuilder(\Gendoria\CommandQueue\Worker\WorkerInterface::class)->getMock();
        $event = $this->getMockBuilder(QueueWorkerRunEvent::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(array($worker, 'test'))
            ->getMockForAbstractClass();
        $this->assertEquals($worker, $event->getWorker());
        $this->assertEquals('test', $event->getSubsystem());
    }    
    
    public function testNullSubsystem()
    {
        $worker = $this->getMockBuilder(\Gendoria\CommandQueue\Worker\WorkerInterface::class)->getMock();
        $event = $this->getMockBuilder(QueueWorkerRunEvent::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(array($worker))
            ->getMockForAbstractClass();
        $this->assertEquals($worker, $event->getWorker());
        $this->assertNull($event->getSubsystem());
    }    
}
