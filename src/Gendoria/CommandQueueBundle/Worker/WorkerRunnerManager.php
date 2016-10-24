<?php

namespace Gendoria\CommandQueueBundle\Worker;

use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of WorkerRunnerManager
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnerManager
{
    /**
     * Worker runner services configuration.
     * 
     * @var array
     */
    private $runners = array();
    
    /**
     * Container.
     * 
     * @var ContainerInterface
     */
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register runner service.
     * 
     * @param string $name Worker name.
     * @param string $id Service ID.
     * @param array $options Worker options.
     * @throws InvalidArgumentException Thrown, when there is no worker runner service registered in container.
     */
    public function addRunnerService($name, $id, array $options = array())
    {
        if (!$this->container->has($id)) {
            throw new InvalidArgumentException("Service container does not have required service registered.");
        }
        $this->runners[$name] = array(
            'id' => $id,
            'options' => $options,
        );
    }
    
    public function has($name)
    {
        return array_key_exists($name, $this->runners);
    }
    
    public function run($name, OutputInterface $output = null)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException("No runner service registered for provided name.");
        }
        /* @var $runner WorkerRunnerInterface */
        $runner = $this->container->get($this->runners[$name]['id']);
        $runner->run($this->runners[$name]['options'], $this->container, $output);
    }
    
    /**
     * Get registered runners.
     * 
     * @return string[]
     */
    public function getRunners()
    {
        return array_keys($this->runners);
    }

}
