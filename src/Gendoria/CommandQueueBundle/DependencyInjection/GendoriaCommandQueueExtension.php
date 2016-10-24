<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection;

use Gendoria\CommandQueue\QueueManager\NullQueueManager;
use Gendoria\CommandQueue\QueueManager\SingleQueueManager;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GendoriaCommandQueueExtension extends Extension implements PrependExtensionInterface
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
        if (!$config['enabled']) {
            $managerDefinition = $container->getDefinition('gendoria_command_queue.manager');
            $managerDefinition->setClass(NullQueueManager::class);
        }
        $this->setupManagers($config, $container);
        if (!$config['listeners']['clear_entity_managers']) {
            $container->removeDefinition('gendoria_command_queue.listener.clear_entity_managers');
        }        
        if (!$config['listeners']['clear_logs']) {
            $container->removeDefinition('gendoria_command_queue.listener.clear_logs');
        }        
    }
    
    private function setupManagers($config, ContainerBuilder $container)
    {
        //Create simple managers for each pool and tag multiple manager to receive all pools
        $managerDefinition = $container->getDefinition('gendoria_command_queue.manager');
        $poolNames = array_keys($config['pools']);
        foreach ($config['pools'] as $poolName => $poolConfig) {
            $definition = new Definition(SingleQueueManager::class);
            $definition->addTag('gendoria_command_queue.send_manager', array('pool' => $poolName));
            $container->setDefinition('gendoria_command_queue.manager.'.$poolName, $definition);
            $managerDefinition->addTag('gendoria_command_queue.send_manager', array('pool' => $poolName, 'default' => $poolName == 'default'));
        }
        //Inject command routing to default manager
        foreach ($config['routes'] as $commandExpression => $poolName) {
            if (!in_array($poolName, $poolNames)) {
                throw new InvalidArgumentException(sprintf("Pool \"%s\" required in command routing is not present.", $poolName));
            }
            $managerDefinition->addMethodCall('addCommandRoute', array($commandExpression, $poolName));
        }
    }
    
    /**
     * Prepend configuration.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $this->disableDoctrineListener($container);
    }
    
    /**
     * Disable clear entity managers listener, if no doctrine bundle is installed.
     * 
     * @param ContainerBuilder $container
     * @return void
     */
    private function disableDoctrineListener(ContainerBuilder $container)
    {
        if (!$container->hasParameter('kernel.bundles')) {
            return;
        }
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['DoctrineBundle'])) {
            $container->prependExtensionConfig($this->getAlias(), array(
                'listeners' => array(
                    'clear_entity_managers' => false
                )
            ));
        }
    }   
}
