<?php

namespace Gendoria\CommandQueueBundle\Event;

use Gendoria\CommandQueue\Worker\WorkerInterface;

/**
 * Event raised, when queue worker is run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueBeforeTranslateEvent extends QueueWorkerRunEvent
{
    /**
     * Command data before translation.
     * 
     * @var mixed
     */
    private $commandData;
    
    /**
     * Class constructor.
     *
     * @param WorkerInterface   $worker Worker processing this command.
     * @param mixed             $commandData
     * @param string            $subsystem Subsystem name.
     */
    public function __construct(WorkerInterface $worker, $commandData, $subsystem = null)
    {
        parent::__construct($worker, $subsystem);
        $this->commandData = $commandData;
    }
    
    /**
     * Get command data before translation.
     * 
     * @return mixed
     */
    public function getCommandData()
    {
        return $this->commandData;
    }
    
    /**
     * Set new command data.
     * 
     * @param mixed $commandData
     */
    public function setCommandData($commandData)
    {
        $this->commandData = $commandData;
    }
}
