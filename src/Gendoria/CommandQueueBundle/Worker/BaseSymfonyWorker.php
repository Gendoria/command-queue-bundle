<?php

namespace Gendoria\CommandQueueBundle\Worker;

use Exception;
use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\ProcessorFactoryInterface;
use Gendoria\CommandQueue\Worker\BaseWorker;
use Gendoria\CommandQueueBundle\Event\QueueEvents;
use Gendoria\CommandQueueBundle\Event\QueueWorkerRunEvent;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base for Symfony based queue workers.
 * 
 * This class takes care of correctly handling long-running process logs, 
 * as well as sending some events on Symfony event bus.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
abstract class BaseSymfonyWorker extends BaseWorker
{
    /**
     * Symfony event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    public function __construct(ProcessorFactoryInterface $processorFactory, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null)
    {
        parent::__construct($processorFactory, $logger);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get subsystem name.
     * 
     * This name may be used further to identify worker subsystem in logs or events.
     * 
     * @return string
     */
    abstract public function getSubsystemName();

    /**
     * {@inheritdoc}
     */
    protected function beforeTranslateHook(&$commandData)
    {
        parent::beforeTranslateHook($commandData);

        $this->eventDispatcher->dispatch(QueueEvents::WORKER_RUN_BEFORE_TRANSLATE, new QueueWorkerRunEvent($this, $this->getSubsystemName()));
        $this->clearLogs();
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessHook(CommandInterface $command, CommandProcessorInterface $processor)
    {
        parent::afterProcessHook($command, $processor);
        $this->eventDispatcher->dispatch(QueueEvents::WORKER_RUN_AFTER_PROCESS, new QueueWorkerRunEvent($this, $this->getSubsystemName()));
        $this->clearLogs();
    }

    /**
     * {@inheritdoc}
     */
    protected function processorErrorHook(CommandInterface $command, CommandProcessorInterface $processor, Exception $e)
    {
        parent::processorErrorHook($command, $processor, $e);
        $this->eventDispatcher->dispatch(QueueEvents::WORKER_RUN_PROCESSOR_ERROR, new QueueWorkerRunEvent($this, $this->getSubsystemName()));
    }

    /**
     * Clear logs.
     * 
     * @return void
     */
    private function clearLogs()
    {
        if (!$this->logger instanceof Logger) {
            return;
        }
        /* @var $logger Logger */
        $logger = $this->logger;

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FingersCrossedHandler) {
                $handler->clear();
            }
        }
    }

}
