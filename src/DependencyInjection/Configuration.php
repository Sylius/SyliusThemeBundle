<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ThemeBundle\DependencyInjection;

use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationSourceFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** @var ConfigurationSourceFactoryInterface[] */
    private array $configurationSourceFactories;

    /**
     * @param ConfigurationSourceFactoryInterface[] $configurationSourceFactories
     */
    public function __construct(array $configurationSourceFactories = [])
    {
        $this->configurationSourceFactories = $configurationSourceFactories;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_theme');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addSourcesConfiguration($rootNode);

        $rootNode->children()->arrayNode('assets')->canBeDisabled();
        $rootNode->children()->arrayNode('templating')->canBeDisabled();
        $rootNode->children()->arrayNode('translations')->canBeDisabled();
        $rootNode->children()->scalarNode('context')->defaultValue('sylius.theme.context.settable')->cannotBeEmpty();
        $rootNode->children()
            ->booleanNode('legacy_mode')
                ->defaultFalse()
                ->setDeprecated('sylius/theme-bundle', '2.0', '"%node%" at path "%path%" is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.')
        ;

        return $treeBuilder;
    }

    private function addSourcesConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $sourcesNodeBuilder = $rootNode
            ->fixXmlConfig('source')
                ->children()
                    ->arrayNode('sources')
                            ->children()
        ;

        foreach ($this->configurationSourceFactories as $sourceFactory) {
            $sourceNode = $sourcesNodeBuilder
                ->arrayNode($sourceFactory->getName())
                ->canBeEnabled()
            ;

            $sourceFactory->buildConfiguration($sourceNode);
        }
    }
}
