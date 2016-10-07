<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gendoria\CommandQueueBundle\DependencyInjection\Pass;

use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class CommandProcessorPass implements CompilerPassInterface
{
    /**
     * Command processor factory service ID.
     *
     * @var string
     */
    protected $processorFactoryService;

    /**
     * Processor listener tag name.
     *
     * @var string
     */
    protected $listenerTag;

    /**
     * Class constructor.
     *
     * @param string $serviceFactoryService Service name of the event dispatcher in processed container.
     * @param string $listenerTag           Tag name used for listener.
     */
    public function __construct($serviceFactoryService = 'gendoria_command_queue.processor_factory', $listenerTag = 'gendoria_command_queue.processor')
    {
        $this->processorFactoryService = $serviceFactoryService;
        $this->listenerTag = $listenerTag;
    }

    /**
     * Process command processor tagged services and add to command processor factory.
     *
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->processorFactoryService) && !$container->hasAlias($this->processorFactoryService)) {
            return;
        }

        $dispatcher = $container->findDefinition($this->processorFactoryService);

        foreach ($container->findTaggedServiceIds($this->listenerTag) as $id => $tags) {
            $def = $container->getDefinition($id);
            if (!$def->isPublic()) {
                throw new InvalidArgumentException(sprintf('The service "%s" must be public as services are lazy-loaded.', $id));
            }

            if ($def->isAbstract()) {
                throw new InvalidArgumentException(sprintf('The service "%s" must not be abstract as services are lazy-loaded.', $id));
            }
            $refl = new ReflectionClass($def->getClass());
            if (!$refl->implementsInterface(CommandProcessorInterface::class)) {
                throw new InvalidArgumentException(sprintf('The service "%s" has to implement '.CommandProcessorInterface::class.'.', $id));
            }
            foreach ($tags as $attributes) {
                if (empty($attributes['command'])) {
                    throw new InvalidArgumentException(sprintf('The service "%s" is tagged as processor without specifying "command" attribute', $id));
                }
                $dispatcher->addMethodCall('registerProcessorIdForCommand', array(
                    $attributes['command'],
                    $id,
                ));
            }
        }
    }
}
