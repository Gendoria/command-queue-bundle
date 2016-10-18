<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueueBundle\DependencyInjection\Pass\WorkersPass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of WorkersPassTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkersPassTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $container = new ContainerBuilder();
        $pass = new WorkersPass();
        $pass->process($container);
    }
}
