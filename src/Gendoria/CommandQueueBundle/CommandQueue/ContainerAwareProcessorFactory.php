<?php

namespace Gendoria\CommandQueueBundle\CommandQueue;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\Exception\MultipleProcessorsException;
use Gendoria\CommandQueue\ProcessorFactory;
use Gendoria\CommandQueue\ProcessorNotFoundException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container aware command queue processor factory.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class ContainerAwareProcessorFactory extends ProcessorFactory
{
    /**
     * Service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Registered services IDs.
     *
     * @var int[]
     */
    private $serviceIds = array();

    /**
     * Class constructor.
     *
     * @param ContainerInterface              $container A ContainerInterface instance
     * @param LoggerInterface Logger instance
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->container = $container;
    }

    /**
     * Register processor service for command class.
     *
     * @param string $commandClassName
     * @param string $serviceId
     * @throws MultipleProcessorsException Thrown, when several processors are registered for given class.
     */
    public function registerProcessorIdForCommand($commandClassName, $serviceId)
    {
        //Maybe the processor is registered in parent
        if ($this->hasProcessor($commandClassName)) {
            throw new MultipleProcessorsException($commandClassName);
        }
        $this->serviceIds[$commandClassName] = $serviceId;
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasProcessor($commandClassName)
    {
        return parent::hasProcessor($commandClassName) || array_key_exists($commandClassName, $this->serviceIds) ;
    }
    
    /**
     * Get processor for class name, if any is registered.
     *
     * {@inheritdoc}
     */
    public function getProcessor(CommandInterface $command)
    {
        $className = get_class($command);
        try {
            return parent::getProcessor($command);
        } catch (ProcessorNotFoundException $ex) {
            if (!array_key_exists($className, $this->serviceIds)) {
                throw new InvalidArgumentException('No processor registered for given type: '.$className.'.', 500, $ex);
            }
            $this->registerProcessorForCommand($className, $this->container->get($this->serviceIds[$className]));
        }

        return parent::getProcessor($command);
    }
}
