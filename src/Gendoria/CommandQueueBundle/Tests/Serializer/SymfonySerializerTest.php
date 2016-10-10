<?php

namespace Gendoria\CommandQueueBundle\Tests\Serializer;

use Exception;
use Gendoria\CommandQueue\Serializer\SerializedCommandData;
use Gendoria\CommandQueue\Worker\Exception\TranslateErrorException;
use Gendoria\CommandQueueBundle\Serializer\SymfonySerializer;
use Gendoria\CommandQueueBundle\Tests\Fixtures\DummyCommand;
use PHPUnit_Framework_TestCase;
use stdClass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Test of symfony serializer
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class SymfonySerializerTest extends PHPUnit_Framework_TestCase
{
    public function testSerializeUnserialize()
    {
        $command = new DummyCommand("testData");
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), array($encoder));
        
        $sfSerializer = new SymfonySerializer($serializer);
        $serialized = $sfSerializer->serialize($command);
        $unserialized = $sfSerializer->unserialize($serialized);
        $this->assertEquals($command, $unserialized);
    }
    
    public function testSerializeException()
    {
        $this->setExpectedException(Exception::class);
        
        $command = new DummyCommand("testData");
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), array());
        
        $sfSerializer = new SymfonySerializer($serializer);
        $sfSerializer->serialize($command);
    }
    
    public function testUnserializeSerializerException()
    {
        $this->setExpectedException(TranslateErrorException::class);
        $serializedCommandData = new SerializedCommandData('--', 'stdClass');
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), array($encoder));
        
        $sfSerializer = new SymfonySerializer($serializer);
        $sfSerializer->unserialize($serializedCommandData);
    }
    
    public function testUnserializeCommandClassException()
    {
        $this->setExpectedException(TranslateErrorException::class, 'Unserialized command should implement CommandInterface.');
        $serializedCommandData = new SerializedCommandData('[]', 'stdClass');
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), array($encoder));
        
        $sfSerializer = new SymfonySerializer($serializer);
        $sfSerializer->unserialize($serializedCommandData);
    }    
}
