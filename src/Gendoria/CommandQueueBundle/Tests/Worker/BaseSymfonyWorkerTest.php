<?php

namespace Gendoria\CommandQueueBundle\Tests\Worker;

use Exception;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\ProcessorFactory\ProcessorFactoryInterface;
use Gendoria\CommandQueue\Worker\Exception\ProcessorErrorException;
use Gendoria\CommandQueueBundle\Event\QueueBeforeGetProcessorEvent;
use Gendoria\CommandQueueBundle\Event\QueueBeforeTranslateEvent;
use Gendoria\CommandQueueBundle\Event\QueueEvents;
use Gendoria\CommandQueueBundle\Event\QueueProcessErrorEvent;
use Gendoria\CommandQueueBundle\Event\QueueProcessEvent;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummyCommand;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummySymfonyWorker;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testing base symfony worker.
 * 
 * In tests we'll use dummy implementation of a worker.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class BaseSymfonyWorkerTest extends PHPUnit_Framework_TestCase
{
    public function testCorrectRun()
    {
        $eventDispatcher = $this->geTMockBuilder(EventDispatcherInterface::class)->getMock();
        $processorFactory = $this->getMockBuilder(ProcessorFactoryInterface::class)->getMock();
        $processor = $this->getMockBuilder(CommandProcessorInterface::class)->getMock();
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fingersCrossed = $this->getMockBuilder(FingersCrossedHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $expectedCommand = new DummyCommand("CommandData");
        
        $worker = new DummySymfonyWorker($processorFactory, $eventDispatcher, $logger);
        
        $processorFactory->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo($expectedCommand))
            ->will($this->returnValue($processor))
            ;
        $processor->expects($this->once())
            ->method('process')
            ->with($this->equalTo($expectedCommand));
        
        $logger->expects($this->exactly(2))
            ->method('getHandlers')
            ->will($this->returnValue(array($fingersCrossed)))
            ;
        $fingersCrossed->expects($this->exactly(2))
            ->method('clear');
        
        $eventDispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_TRANSLATE), $this->isInstanceOf(QueueBeforeTranslateEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_GET_PROCESSOR), $this->isInstanceOf(QueueBeforeGetProcessorEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_PROCESS), $this->isInstanceOf(QueueProcessEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_AFTER_PROCESS), $this->isInstanceOf(QueueProcessEvent::class))
                );
        
        $worker->process("CommandData");
    }
    
    public function testProcessErrorRun()
    {
        $this->setExpectedException(ProcessorErrorException::class);
        $eventDispatcher = $this->geTMockBuilder(EventDispatcherInterface::class)->getMock();
        $processorFactory = $this->getMockBuilder(ProcessorFactoryInterface::class)->getMock();
        $processor = $this->getMockBuilder(CommandProcessorInterface::class)->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $expectedCommand = new DummyCommand("CommandData");
        
        $worker = new DummySymfonyWorker($processorFactory, $eventDispatcher, $logger);
        
        $processorFactory->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo($expectedCommand))
            ->will($this->returnValue($processor))
            ;
        $processor->expects($this->once())
            ->method('process')
            ->with($this->equalTo($expectedCommand))
            ->will($this->throwException(new Exception("Dummy")));
        
        $eventDispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_TRANSLATE), $this->isInstanceOf(QueueBeforeTranslateEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_GET_PROCESSOR), $this->isInstanceOf(QueueBeforeGetProcessorEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_BEFORE_PROCESS), $this->isInstanceOf(QueueProcessEvent::class)),
                array($this->equalTo(QueueEvents::WORKER_RUN_PROCESSOR_ERROR), $this->isInstanceOf(QueueProcessErrorEvent::class))
                );
        
        $worker->process("CommandData");
    }
}
