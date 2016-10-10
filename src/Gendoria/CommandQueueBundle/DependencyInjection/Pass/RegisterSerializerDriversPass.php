<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection\Pass;

use Gendoria\CommandQueue\Serializer\NullSerializer;
use Gendoria\CommandQueueBundle\Serializer\JmsSerializer;
use Gendoria\CommandQueueBundle\Serializer\SymfonySerializer;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Serializer;

/**
 * Description of RegisterDriversPass
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class RegisterSerializerDriversPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->registerNullDriver($container);
        $this->registerSymfonyDriver($container);
        $this->registerJmsDriver($container);
    }
    
    private function registerNullDriver(ContainerBuilder $container)
    {
        $container->addDefinitions(array(
            'gendoria_command_queue.serializer.null' => new Definition(NullSerializer::class),
        ));
    }

    private function registerSymfonyDriver(ContainerBuilder $container)
    {
        if (class_exists(Serializer::class) && $container->hasDefinition('serializer')) {
            $definition = new Definition(SymfonySerializer::class);
            $definition->addArgument(new Reference('serializer'));
            $container->addDefinitions(array('gendoria_command_queue.serializer.symfony' => $definition));
        }
    }
    
    private function registerJmsDriver(ContainerBuilder $container)
    {
        if (class_exists(JMSSerializerBundle::class)) {
            $definition = new Definition(JmsSerializer::class);
            $definition->addArgument(new Reference('jms_serializer'));
            $container->addDefinitions(array('gendoria_command_queue.serializer.jms' => $definition));
        }
    }
}