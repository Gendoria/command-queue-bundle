<?php

namespace Gendoria\CommandQueueBundle\Listener;

use Gendoria\CommandQueueBundle\Event\QueueBeforeTranslateEvent;
use Gendoria\CommandQueueBundle\Event\QueueEvents;
use Gendoria\CommandQueueBundle\Event\QueueProcessEvent;
use Gendoria\CommandQueueBundle\Event\QueueWorkerRunEvent;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Clears logs before and after each worker run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class ClearLogsListener implements EventSubscriberInterface
{
    /**
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Class constructor.
     * 
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Get subscribed events.
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            QueueEvents::WORKER_RUN_BEFORE_TRANSLATE => 'beforeQueueRun',
            QueueEvents::WORKER_RUN_AFTER_PROCESS => 'afterQueueRun',
        );
    }
    
    /**
     * Invoke before queue run.
     * 
     * @param QueueWorkerRunEvent $e
     */
    public function beforeQueueRun(QueueBeforeTranslateEvent $e)
    {
        $this->clearLogs();
    }
    
    /**
     * Invoke after queue run.
     * 
     * @param QueueWorkerRunEvent $e
     */
    public function afterQueueRun(QueueProcessEvent $e)
    {
        $this->clearLogs();
    }
    
    /**
     * Clear logs on fingers crossed logger handlers.
     */
    private function clearLogs()
    {
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof FingersCrossedHandler) {
                $handler->clear();
            }
        }
    }
}
