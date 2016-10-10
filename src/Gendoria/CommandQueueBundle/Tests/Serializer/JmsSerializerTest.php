<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests\Serializer;

use Exception;
use Gendoria\CommandQueue\Serializer\SerializedCommandData;
use Gendoria\CommandQueue\Worker\Exception\TranslateErrorException;
use Gendoria\CommandQueueBundle\Serializer\JmsSerializer;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummyCommand;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummyInvalidJmsCommand;
use JMS\Serializer\SerializerBuilder;
use PHPUnit_Framework_TestCase;

/**
 * Description of JmsSerializerTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class JmsSerializerTest extends PHPUnit_Framework_TestCase
{
    public function testSerializeUnserialize()
    {
        $command = new DummyCommand("testData");
        $serializer = SerializerBuilder::create()->build();
        
        $jmsSerializer = new JmsSerializer($serializer);
        $serialized = $jmsSerializer->serialize($command);
        $unserialized = $jmsSerializer->unserialize($serialized);
        $this->assertEquals($command, $unserialized);
            
    }
    
    public function testSerializeException()
    {
        $this->setExpectedException(Exception::class, "Class object does not exist");
        
        $command = new DummyInvalidJmsCommand("testData");
        $serializer = SerializerBuilder::create()->build();
        
        $jmsSerializer = new JmsSerializer($serializer);
        $jmsSerializer->serialize($command);
    }
    
    public function testUnserializeSerializerException()
    {
        $this->setExpectedException(TranslateErrorException::class, 'Could not decode JSON, syntax error - malformed JSON.');
        $serializedCommandData = new SerializedCommandData('', 'stdClass');
        $serializer = SerializerBuilder::create()->build();
        $jmsSerializer = new JmsSerializer($serializer);
        $jmsSerializer->unserialize($serializedCommandData);
    }
    
    public function testUnserializeCommandClassException()
    {
        $this->setExpectedException(TranslateErrorException::class, 'Unserialized command should implement CommandInterface.');
        $serializedCommandData = new SerializedCommandData('[]', 'stdClass');
        $serializer = SerializerBuilder::create()->build();
        $jmsSerializer = new JmsSerializer($serializer);
        $jmsSerializer->unserialize($serializedCommandData);
    }
    
}
