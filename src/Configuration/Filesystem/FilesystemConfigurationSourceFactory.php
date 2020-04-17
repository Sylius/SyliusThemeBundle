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

namespace Sylius\Bundle\ThemeBundle\Configuration\Filesystem;

use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProcessorInterface;
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationSourceFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface;
use Sylius\Bundle\ThemeBundle\Locator\RecursiveFileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class FilesystemConfigurationSourceFactory implements ConfigurationSourceFactoryInterface
{
    public function buildConfiguration(ArrayNodeDefinition $node): void
    {
        $filesystemNode = $node->fixXmlConfig('directory', 'directories')->children();

        $filesystemNode
            ->scalarNode('filename')
                ->defaultValue('composer.json')
                ->cannotBeEmpty()
        ;

        $filesystemNode
            ->scalarNode('scan_depth')
                ->info('Restrict depth to scan for configuration file inside theme folder')
                ->defaultValue(1)
        ;

        $filesystemNode
            ->arrayNode('directories')
                ->defaultValue(['%kernel.project_dir%/themes'])
                ->requiresAtLeastOneElement()
                ->performNoDeepMerging()
                ->prototype('scalar')
        ;
    }

    public function initializeSource(ContainerBuilder $container, array $config): Definition
    {
        $recursiveFileLocator = new Definition(RecursiveFileLocator::class, [
            new Reference(FinderFactoryInterface::class),
            $config['directories'],
            $config['scan_depth'],
        ]);

        $configurationLoader = new Definition(ProcessingConfigurationLoader::class, [
            new Definition(JsonFileConfigurationLoader::class, [
                new Reference(FilesystemInterface::class),
            ]),
            new Reference(ConfigurationProcessorInterface::class),
        ]);

        return new Definition(FilesystemConfigurationProvider::class, [
            $recursiveFileLocator,
            $configurationLoader,
            $config['filename'],
        ]);
    }

    public function getName(): string
    {
        return 'filesystem';
    }
}
