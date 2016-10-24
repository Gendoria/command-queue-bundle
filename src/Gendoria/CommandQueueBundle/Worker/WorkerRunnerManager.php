<?php

namespace Gendoria\CommandQueueBundle\Worker;

use Gendoria\CommandQueue\Worker\WorkerRunnerManager as BaseRunnerManager;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of WorkerRunnerManager
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnerManager extends BaseRunnerManager
{
    /**
     * Worker runner services configuration.
     * 
     * @var array
     */
    private $runnerServices = array();
    
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
        $this->runnerServices[$name] = array(
            'id' => $id,
            'options' => $options,
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if (parent::has($name)) {
            return true;
        }
        return !empty($this->runnerServices[$name]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function run($name, OutputInterface $output = null)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException("No runner service registered for provided name.");
        }
        if (!parent::has($name)) {
            $runner = $this->container->get($this->runnerServices[$name]['id']);
            $this->addRunner($name, $runner, $this->runnerServices[$name]['options']);
        }
        parent::run($name, $output);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRunners()
    {
        return array_merge(array_keys($this->runnerServices), parent::getRunners());
    }

}
