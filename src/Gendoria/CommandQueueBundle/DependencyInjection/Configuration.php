<?php

namespace Gendoria\CommandQueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Configuration alias.
     *
     * @var string
     */
    private $alias;

    /**
     * Class constructor.
     *
     * @param string $alias Root configuration key.
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get configuration tree builder instance.
     *
     * @return TreeBuilder Tree builder instance.
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);
        $rootNode->children()
            ->scalarNode('enable')
                ->defaultTrue()
            ->end()
            ->arrayNode('pools')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->validate()
                ->ifTrue(function (array $value) {
                        return !array_key_exists('default', $value);
                })
                    ->thenInvalid('Default service not present')
                ->end()
                ->prototype('array')
                    ->children()
                        ->scalarNode('send_driver')
                            ->isRequired()
                            ->validate()
                            ->ifTrue(function ($value) {
                                    return !preg_match('/^@[a-zA-Z\.\-0-9\_]+$/', $value);
                            })
                                ->thenInvalid('Malformed service ID "%s"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('routes')
                ->normalizeKeys(false)
                ->prototype('scalar')
                    ->isRequired()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
