<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\Worker\WorkerInterface;
use Gendoria\CommandQueueBundle\Event\QueueBeforeTranslateEvent;
use PHPUnit_Framework_TestCase;

/**
 * Description of QueueBeforeGetProcessorEventTest
 *
 * @author Tomasz Struczyński <tomasz.struczynski@isobar.com>
 */
class QueueBeforeTranslateEventTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $worker = $this->getMockBuilder(WorkerInterface::class)->getMock();
        $command = $this->getMockBuilder(CommandInterface::class)->getMock();
        $event = new QueueBeforeTranslateEvent($worker, $command);
        $this->assertEquals($command, $event->getCommandData());
        $event->setCommandData("ttt");
        $this->assertEquals("ttt", $event->getCommandData());
    }
}
