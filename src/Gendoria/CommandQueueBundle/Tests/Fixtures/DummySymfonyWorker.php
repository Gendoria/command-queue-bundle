<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests\Fixtures;

use Gendoria\CommandQueue\ProcessorFactory\ProcessorFactoryInterface;
use Gendoria\CommandQueue\Serializer\NullSerializer;
use Gendoria\CommandQueue\Serializer\SerializedCommandData;
use Gendoria\CommandQueue\Worker\Exception\TranslateErrorException;
use Gendoria\CommandQueueBundle\Worker\BaseSymfonyWorker;
use Psr\Log\LoggerInterface;
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
        $serializer = new NullSerializer();
        parent::__construct($processorFactory, $serializer, $eventDispatcher, $logger);
        $this->translateFailure = (bool)$translateFailure;
    }
    
    protected function getSerializedCommandData($commandData)
    {
        if ($this->translateFailure) {
            throw new TranslateErrorException("Dummy exception");
        }
        return new SerializedCommandData(new DummyCommand($commandData), DummyCommand::class);
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
