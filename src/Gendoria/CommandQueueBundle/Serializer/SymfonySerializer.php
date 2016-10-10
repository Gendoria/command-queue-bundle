<?php

namespace Gendoria\CommandQueueBundle\Serializer;

use Exception;
use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\Serializer\SerializedCommandData;
use Gendoria\CommandQueue\Serializer\SerializerInterface;
use Gendoria\CommandQueue\Worker\Exception\TranslateErrorException;
use Symfony\Component\Serializer\Serializer;

/**
 * Description of SymfonySerializer
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class SymfonySerializer implements SerializerInterface
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
    
    public function serialize(CommandInterface $command)
    {
        return new SerializedCommandData($this->serializer->serialize($command, $this->format), get_class($command));
    }

    public function unserialize(SerializedCommandData $serializedCommandData)
    {
        try {
            $command = $this->serializer->deserialize($serializedCommandData->getSerializedCommand(), $serializedCommandData->getCommandClass(), $this->format);
        } catch (Exception $e) {
            throw new TranslateErrorException($serializedCommandData, $e->getMessage(), $e->getCode(), $e);
        }
        if (!is_object($command) || !$command instanceof CommandInterface) {
            throw new TranslateErrorException($serializedCommandData, "Unserialized command should implement CommandInterface.");
        }
        return $command;
    }

}
