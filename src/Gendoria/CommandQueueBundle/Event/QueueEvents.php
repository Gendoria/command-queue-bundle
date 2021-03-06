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
     * This event is thrown each time a worker is run for a command 
     * before translating command data to command.
     *
     * The event listener receives a QueueWorkerRunEvent.
     *
     * @see QueueWorkerRunEvent
     *
     * @var string
     */
    const WORKER_RUN_BEFORE_TRANSLATE = 'gendoria_command_queue.worker_run.before_translate';
    
    /**
     * This event is thrown each time a worker is run for a command
     * before getting processor for a command.
     *
     * The event listener receives a QueueWorkerRunEvent.
     *
     * @see QueueWorkerRunEvent
     *
     * @var string
     */
    const WORKER_RUN_BEFORE_GET_PROCESSOR = 'gendoria_command_queue.worker_run.before_get_processor';
    
    /**
     * This event is thrown each time a worker is run for a command
     * before actually processing a command by a processor.
     *
     * The event listener receives a QueueWorkerRunEvent.
     *
     * @see QueueWorkerRunEvent
     *
     * @var string
     */
    const WORKER_RUN_BEFORE_PROCESS = 'gendoria_command_queue.worker_run.before_process';
    
    /**
     * This event is thrown after each successfull worker run.
     * 
     * @see QueueWorkerRunEvent
     * @var string
     */
    const WORKER_RUN_AFTER_PROCESS = 'gendoria_command_queue.worker_run.after_process';
    
    /**
     * This event is thrown after each unsuccessfull worker run.
     * 
     * @see QueueWorkerRunEvent
     * @var string
     */
    const WORKER_RUN_PROCESSOR_ERROR = 'gendoria_command_queue.worker_run.processor_error';
}
