<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests\Fixtures;

use Gendoria\CommandQueue\ProcessorFactoryInterface;
use Gendoria\CommandQueueBundle\Worker\BaseSymfonyWorker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Dummy symfony worker used for testing abstract symfony worker class.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class DummySymfonyWorker extends BaseSymfonyWorker
{
    /**
     * If set, command translation will result in failure.
     * @var boolean
     */
    private $translateFailure;
    
    public function __construct(ProcessorFactoryInterface $processorFactory, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null, $translateFailure = false)
    {
        parent::__construct($processorFactory, $eventDispatcher, $logger);
        $this->translateFailure = (bool)$translateFailure;
    }
    
    /**
     * Implementation of translate command method.
     * 
     * @param mixed $commandData
     * @return DummyCommand
     * @throws Exception
     */
    protected function translateCommand($commandData)
    {
        if ($this->translateFailure) {
            throw new Exception("Dummy exception");
        }
        
        return new DummyCommand($commandData);
    }

    /**
     * Implementation of get subsystem name method.
     * 
     * @return string
     */
    public function getSubsystemName()
    {
        return 'test';
    }

}
