<?php

namespace Gendoria\CommandQueueBundle\Event;

use Exception;
use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\Worker\WorkerInterface;

/**
 * Event raised, when queue worker is run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueProcessErrorEvent extends QueueProcessEvent
{
    /**
     * Exception.
     * 
     * @var Exception
     */
    private $exception;
    
    /**
     * Class constructor.
     *
     * @param WorkerInterface           $worker Worker processing this command.
     * @param CommandInterface          $command
     * @param CommandProcessorInterface $processor
     * @param Exception                 $e
     * @param string                    $subsystem Subsystem name.
     */
    public function __construct(WorkerInterface $worker, CommandInterface $command, CommandProcessorInterface $processor, Exception $e, $subsystem = null)
    {
        parent::__construct($worker, $command, $processor, $subsystem);
        $this->exception = $e;
    }
    
    /**
     * Return exception.
     * 
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
