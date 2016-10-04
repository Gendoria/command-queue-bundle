<?php

namespace Gendoria\CommandQueueBundle\Event;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\Worker\WorkerInterface;

/**
 * Event raised, when queue worker is run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueBeforeGetProcessorEvent extends QueueWorkerRunEvent
{
    /**
     * Command data before translation.
     * 
     * @var CommandInterface
     */
    private $command;
    
    /**
     * Class constructor.
     *
     * @param WorkerInterface   $worker Worker processing this command.
     * @param CommandInterface  $command
     * @param string            $subsystem Subsystem name.
     */
    public function __construct(WorkerInterface $worker, CommandInterface $command, $subsystem = null)
    {
        parent::__construct($worker, $subsystem);
        $this->command = $command;
    }
    
    /**
     * Get command data before translation.
     * 
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }    
}
