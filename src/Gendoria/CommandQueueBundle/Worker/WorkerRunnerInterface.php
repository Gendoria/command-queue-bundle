<?php

namespace Gendoria\CommandQueueBundle\Worker;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Worker runner interface describes functions needed to register service as worker runner.
 * 
 * Worker runner service can be run using one console command, independent of the driver used.
 * 
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
interface WorkerRunnerInterface
{
    /**
     * Run worker with provided options.
     * 
     * @param array $options
     * @param ContainerInterface $container
     * @param OutputInterface $output
     * @throws InvalidArgumentException Thrown, if options array is incorrect for this worker.
     * @throws Exception Thrown, if worker could not be run or resulted in error.
     */
    public function run(array $options, ContainerInterface $container, OutputInterface $output = null);
}
