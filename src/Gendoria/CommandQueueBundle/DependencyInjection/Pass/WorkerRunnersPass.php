<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection\Pass;

use Gendoria\CommandQueueBundle\Worker\WorkerRunnerInterface;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of WorkersPass
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnersPass implements CompilerPassInterface
{

    const WORKER_RUNNER_TAG = 'gendoria_command_queue.worker';
    const MANAGER_ID = 'gendoria_command_queue.runner_manager';

    /**
     * Process all services tagged as worker runners and add them to runner manager service.
     * 
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException Thrown, if service initialization failed.
     */
    public function process(ContainerBuilder $container)
    {
        $manager = $container->findDefinition(self::MANAGER_ID);

        $serviceIds = $container->findTaggedServiceIds(self::WORKER_RUNNER_TAG);
        foreach ($serviceIds as $serviceId => $tags) {
            $this->addManagerCalls($container, $manager, $serviceId, $tags);
        }
    }

    /**
     * Parse single tagged service.
     * 
     * @param ContainerBuilder $container
     * @param Definition $manager
     * @param string $serviceId
     * @param array $tags
     * @throws InvalidArgumentException Thrown, if service initialization failed.
     */
    private function addManagerCalls(ContainerBuilder $container, Definition $manager, $serviceId, array $tags)
    {
        foreach ($tags as $tag) {
            $this->addManagerCall($container, $manager, $serviceId, $tag);
        }
    }

    /**
     * Parse single tagged service tag.
     * 
     * @param ContainerBuilder $container
     * @param Definition $manager
     * @param string $serviceId
     * @param array $tag
     * @throws InvalidArgumentException Thrown, if service initialization failed.
     */
    private function addManagerCall(ContainerBuilder $container, Definition $manager, $serviceId, array $tag)
    {
        $this->assertCorrectName($tag);
        $this->assertCorrectService($container, $serviceId);
        $options = !empty($tag['options']) ? json_decode($tag['options'], true) : array();
        $this->assertCorrectOptions($options);
        $manager->addMethodCall('addRunner', array($tag['name'], $serviceId, $options));
    }
    
    /**
     * Assert correct tag structure.
     * 
     * @param array $tag
     * @throws InvalidArgumentException Thrown, if tag does not contain correct fields.
     */
    private function assertCorrectName(array $tag)
    {
        if (empty($tag['name'])) {
            throw new InvalidArgumentException('Tag '.self::WORKER_RUNNER_TAG.' has to contain "name" parameter.');
        }
    }
    
    /**
     * Assert correct options structure.
     * 
     * @param moxed $options
     * @throws InvalidArgumentException Thrown, if options are invalid (not an array).
     */
    private function assertCorrectOptions($options)
    {
        if (!is_array($options)) {
            throw new InvalidArgumentException('Options parameter has to be a valid JSON.');
        }
    }
    
    /**
     * Assert valid tagged service.
     * 
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @throws InvalidArgumentException Thrown, if tagged service does not implement correct interfaces.
     */
    private function assertCorrectService(ContainerBuilder $container, $serviceId)
    {
        $definition = $container->findDefinition($serviceId);
        $reflection = new ReflectionClass($definition->getClass());
        if (!$reflection->implementsInterface(WorkerRunnerInterface::class)) {
            throw new InvalidArgumentException('Runner service has to implement WorkerRunnerInterface.');
        }
    }
}
