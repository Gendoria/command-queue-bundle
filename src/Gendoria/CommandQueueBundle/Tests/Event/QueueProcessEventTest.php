<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\Worker\WorkerInterface;
use Gendoria\CommandQueueBundle\Event\QueueProcessEvent;
use PHPUnit_Framework_TestCase;

/**
 * Description of QueueBeforeGetProcessorEventTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueProcessEventTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $worker = $this->getMockBuilder(WorkerInterface::class)->getMock();
        $command = $this->getMockBuilder(CommandInterface::class)->getMock();
        $processor = $this->getMockBuilder(CommandProcessorInterface::class)->getMock();
        $event = new QueueProcessEvent($worker, $command, $processor);
        $this->assertEquals($command, $event->getCommand());
        $this->assertEquals($processor, $event->getProcessor());
    }
}
