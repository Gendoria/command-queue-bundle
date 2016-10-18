<?php

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueueBundle\DependencyInjection\Pass\WorkerRunnersPass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of WorkersPassTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnersPassTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $container = new ContainerBuilder();
        $pass = new WorkerRunnersPass();
        $pass->process($container);
    }
}
