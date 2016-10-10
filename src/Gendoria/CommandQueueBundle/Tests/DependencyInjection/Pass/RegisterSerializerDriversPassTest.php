<?php

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueueBundle\DependencyInjection\Pass\RegisterSerializerDriversPass;
use JMS\Serializer\Serializer as Serializer2;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests of pools pass
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 * @group CommandQueue
 * @group legacy
 */
class RegisterSerializerDriversPassTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'serializer' => new Definition(Serializer::class),
        ));
        $pass = new RegisterSerializerDriversPass();
        $pass->process($container);
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.serializer.null'));
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.serializer.symfony'));
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.serializer.jms'));
    }
    
    public function testJmsAlias()
    {
        $container = new ContainerBuilder();
        $container->addDefinitions(array(
            'jms_serializer' => new Definition(Serializer2::class),
        ));
        $container->addAliases(array(
            'serializer' => new Alias('jms_serializer'),
        ));
        $pass = new RegisterSerializerDriversPass();
        $pass->process($container);
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.serializer.null'));
        $this->assertFalse($container->hasDefinition('gendoria_command_queue.serializer.symfony'));
        $this->assertTrue($container->hasDefinition('gendoria_command_queue.serializer.jms'));
    }
}
