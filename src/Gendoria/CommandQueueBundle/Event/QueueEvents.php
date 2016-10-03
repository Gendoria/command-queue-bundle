<?php

namespace Gendoria\CommandQueueBundle\Event;

/**
 * This class describes all events for CommandQueueBundle.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
final class QueueEvents
{
    /**
     * This event is thrown each time a worker is run for a command.
     *
     * The event listener receives a QueueWorkerRunEvent.
     *
     * @see QueueWorkerRunEvent
     *
     * @var string
     */
    const WORKER_RUN_BEFORE = 'gendoria_command_queue.worker_run.before';
    
    /**
     * This event is thrown after each successfull worker run.
     * 
     * @see QueueWorkerRunEvent
     * @var string
     */
    const WORKER_RUN_AFTER = 'gendoria_command_queue.worker_run.after';
    
    /**
     * This event is thrown after each unsuccessfull worker run.
     * 
     * @see QueueWorkerRunEvent
     * @var string
     */
    const WORKER_RUN_ERROR = 'gendoria_command_queue.worker_run.error';
}
