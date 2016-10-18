<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection\Pass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of WorkersPass
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkersPass implements CompilerPassInterface
{
    const WORKER_TAG = 'gendoria_command_queue.worker';
    
    public function process(ContainerBuilder $container)
    {
        
    }
}
