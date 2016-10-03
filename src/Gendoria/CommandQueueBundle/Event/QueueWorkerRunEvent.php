<?php

namespace Gendoria\CommandQueueBundle\Event;

use Gendoria\CommandQueue\Worker\WorkerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event raised, when queue worker is run.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class QueueWorkerRunEvent extends Event
{
    /**
     * Subsystem name.
     *
     * @var string|null
     */
    private $subsystem;
    
    /**
     * Worker processing this request.
     * 
     * @var WorkerInterface
     */
    private $worker;

    /**
     * Class constructor.
     *
     * @param WorkerInterface $worker Worker processing this command.
     * @param string          $subsystem Subsystem name.
     */
    public function __construct(WorkerInterface $worker, $subsystem = null)
    {
        $this->worker = $worker;
        $this->subsystem = $subsystem ? (string) $subsystem : null;
    }

    /**
     * Get subsystem name.
     *
     * @return string|null
     */
    public function getSubsystem()
    {
        return $this->subsystem;
    }
    
    /**
     * Get worker processing this request.
     * 
     * @return WorkerInterface
     */
    public function getWorker()
    {
        return $this->worker;
    }
}
