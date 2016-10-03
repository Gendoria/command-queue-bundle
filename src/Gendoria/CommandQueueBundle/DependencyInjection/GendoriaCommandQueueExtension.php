<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection;

use Gendoria\CommandQueue\QueueManager\NullQueueManager;
use Gendoria\CommandQueue\QueueManager\SimpleQueueManager;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GendoriaCommandQueueExtension extends Extension
{
    /**
     * Get extension alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return 'gendoria_command_queue';
    }

    /**
     * Load extension.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException Thrown, when pool required in configuration is not present.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('gendoria_command_queue.pools', $config['pools']);
        if (!$config['enable']) {
            $managerDefinition = $container->getDefinition('gendoria_command_queue.manager');
            $managerDefinition->setClass(NullQueueManager::class);
        }
        $this->setupManagers($config, $container);
    }
    
    private function setupManagers($config, ContainerBuilder $container)
    {
        //Create simple managers for each pool and tag multiple manager to receive all pools
        $managerDefinition = $container->getDefinition('gendoria_command_queue.manager');
        $poolNames = array_keys($config['pools']);
        foreach ($config['pools'] as $poolName => $poolConfig) {
            $definition = new Definition(SimpleQueueManager::class);
            $definition->addTag('gendoria_command_queue.send_manager', array('pool' => $poolName));
            $container->setDefinition('gendoria_command_queue.manager.'.$poolName, $definition);
            $managerDefinition->addTag('gendoria_command_queue.send_manager', array('pool' => $poolName, 'default' => $poolName == 'default'));
        }
        //Inject command routing to default manager
        foreach ($config['routes'] as $commandExpression => $poolName) {
            if (!in_array($poolName, $poolNames)) {
                throw new InvalidArgumentException(sprintf("Pool \"%s\" required in command routing is not present", $poolName));
            }
            $managerDefinition->addMethodCall('addCommandRoute', array($commandExpression, $poolName));
        }
    }
}
