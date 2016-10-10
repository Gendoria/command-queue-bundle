<?php

namespace Gendoria\CommandQueueBundle\Serializer;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\Serializer\SerializedCommandData;
use Gendoria\CommandQueue\Serializer\SerializerInterface;
use Gendoria\CommandQueue\Worker\Exception\TranslateErrorException;
use JMS\Serializer\Serializer;

/**
 * Serializer using JMS serializer module
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class JmsSerializer implements SerializerInterface
{
    /**
     *
     * @var Serializer
     */
    private $serializer;
    
    /**
     * Serialization format.
     * 
     * @var string
     */
    private $format;
    
    /**
     * Class constructor.
     * 
     * @param Serializer $serializer
     * @param string $format Serialization format.
     */
    public function __construct(Serializer $serializer, $format = "json")
    {
        $this->serializer = $serializer;
        $this->format = $format;
    }
    
    /**
     * {@inheritdoc}
     */
    public function serialize(CommandInterface $command)
    {
        return new SerializedCommandData($this->serializer->serialize($command, $this->format), get_class($command));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize(SerializedCommandData $serializedCommandData)
    {
        try {
            $command = $this->serializer->deserialize($serializedCommandData->getSerializedCommand(), $serializedCommandData->getCommandClass(), $this->format);
        } catch (\Exception $e) {
            throw new TranslateErrorException($serializedCommandData, $e->getMessage(), $e->getCode(), $e);
        }
        if (!is_object($command) || !$command instanceof CommandInterface) {
            throw new TranslateErrorException($serializedCommandData, "Unserialized command should implement CommandInterface.");
        }
        return $command;
    }
}
