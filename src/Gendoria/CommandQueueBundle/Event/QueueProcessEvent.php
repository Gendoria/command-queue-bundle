<?php

namespace Gendoria\CommandQueueBundle\Event;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Gendoria\CommandQueue\Worker\WorkerInterface;

/**
 * Event raised, when queue worker is run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueProcessEvent extends QueueWorkerRunEvent
{
    /**
     * Command.
     * 
     * @var CommandInterface
     */
    private $command;
    
    /**
     * Command processor.
     * 
     * @var CommandProcessorInterface
     */
    private $processor;

    /**
     * Class constructor.
     *
     * @param WorkerInterface           $worker Worker processing this command.
     * @param CommandInterface          $command
     * @param CommandProcessorInterface $processor
     * @param string                    $subsystem Subsystem name.
     */
    public function __construct(WorkerInterface $worker, CommandInterface $command, CommandProcessorInterface $processor, $subsystem = null)
    {
        parent::__construct($worker, $subsystem);
        $this->command = $command;
        $this->processor = $processor;
    }
    
    /**
     * Get command.
     * 
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get command processor.
     * 
     * @return CommandProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}
