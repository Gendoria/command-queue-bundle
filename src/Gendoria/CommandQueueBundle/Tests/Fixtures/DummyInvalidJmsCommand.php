<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests\Fixtures;

use Gendoria\CommandQueue\Command\CommandInterface;
use JMS\Serializer\Annotation\Type;

/**
 * Dummy command to be returned by dummy worker.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class DummyInvalidJmsCommand implements CommandInterface
{
    /**
     * Command data passed to translator.
     * 
     * @var mixed
     * @Type("object")
     */
    public $commandData;
    
    /**
     * Command data passed to translator.
     * 
     * @param mixed $commandData
     */
    public function __construct($commandData)
    {
        $this->commandData = $commandData;
    }
}
