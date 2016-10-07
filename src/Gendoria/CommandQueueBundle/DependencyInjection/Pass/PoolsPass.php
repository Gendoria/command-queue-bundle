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

use Gendoria\CommandQueue\QueueManager\MultipleQueueManagerInterface;
use Gendoria\CommandQueue\QueueManager\QueueManagerInterface;
use Gendoria\CommandQueue\SendDriver\SendDriverInterface;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class PoolsPass implements CompilerPassInterface
{

    /**
     * Queue manager tag name.
     *
     * @var string
     */
    const QUEUE_MANAGER_TAG = 'gendoria_command_queue.send_manager';

    /**
     * Prepares command queue pools based on bundle configuration.
     *
     * Function also attaches specific pools to services tagged with {@link QUEUE_MANAGER_TAG} tag.
     *
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $this->setupPools($container);
        
        $pools = $container->getParameter('gendoria_command_queue.pools');

        foreach ($container->findTaggedServiceIds(self::QUEUE_MANAGER_TAG) as $id => $tags) {
            $def = $container->getDefinition($id);
            $reflection = new ReflectionClass($def->getClass());
            if ($reflection->implementsInterface(QueueManagerInterface::class)) {
                $this->setupSingleQueueManager($id, $def, $tags, $pools);
            } elseif ($reflection->implementsInterface(MultipleQueueManagerInterface::class)) {
                $this->setupMultipleQueueManager($id, $def, $tags, $pools);
            } else {
                throw new InvalidArgumentException(sprintf('Service "%s" does not implement one of required interfaces.', $id));
            }
        }
    }
    
    /**
     * Setup pools.
     * 
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     * @return void
     */
    private function setupPools(ContainerBuilder $container)
    {
        $pools = $container->getParameter('gendoria_command_queue.pools');
        $usedServiceIds = array();
        foreach ($pools as $poolData) {
            $sendServiceId = substr($poolData['send_driver'], 1);
            if (in_array($sendServiceId, $usedServiceIds)) {
                throw new InvalidArgumentException(sprintf(
                    'Each pool has to have unique send service - duplicate service id "%s" found.',
                    $sendServiceId
                ));
            }
            if (!$container->hasDefinition($sendServiceId)) {
                throw new InvalidArgumentException('Non existing send driver service provided: ' . $poolData['send_driver'].'.');
            }
            $sendDriverReflection = new ReflectionClass($container->getDefinition($sendServiceId)->getClass());
            if (!$sendDriverReflection->implementsInterface(SendDriverInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Service "%s" does not implement interface "%s".',
                    $sendServiceId,
                    SendDriverInterface::class
                ));
            }
            $usedServiceIds[] = $sendServiceId;
        }
    }

    private function setupSingleQueueManager($id, Definition $def, array $tags, array $pools)
    {
        if (count($tags) > 1) {
            throw new InvalidArgumentException('Only single ' . self::QUEUE_MANAGER_TAG . ' tag possible on service ' . $id.'.');
        }
        if (!empty($tags[0]['pool'])) {
            $poolName = $tags[0]['pool'];
        } else {
            $poolName = 'default';
        }
        if (empty($pools[$poolName])) {
            throw new InvalidArgumentException(sprintf(
                'Service "%s" requests non existing queue pool "%s".',
                $id,
                $poolName
            ));
        }
        $def->addMethodCall(
            'setSendDriver',
            array(new Reference(substr($pools[$poolName]['send_driver'], 1)))
        );
    }

    private function setupMultipleQueueManager($id, Definition $def, array $tags, array $pools)
    {
        foreach ($tags as $tag) {
            if (!empty($tag['pool'])) {
                $poolName = $tag['pool'];
            } else {
                $poolName = 'default';
            }
            $isDefault = !empty($tag['default']);
            if (empty($pools[$poolName])) {
                throw new InvalidArgumentException(sprintf(
                    'Service "%s" requests non existing queue pool "%s".',
                    $id,
                    $poolName
                ));
            }
            $def->addMethodCall(
                'addSendDriver',
                array($poolName, new Reference(substr($pools[$poolName]['send_driver'], 1)), $isDefault)
            );
        }
    }
}
