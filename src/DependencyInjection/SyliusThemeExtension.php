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

use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationSourceFactoryInterface;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusThemeExtension extends Extension
{
    /** @var ConfigurationSourceFactoryInterface[] */
    private array $configurationSourceFactories = [];

    /**
     * @internal
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        // Check if we should skip loading services when no themes exist
        $shouldOptimize = $config['optimize_empty'];
        $hasThemes = !$shouldOptimize || $this->hasThemes($container, $config);

        // Store parameters
        $container->setParameter('sylius_theme.optimize_empty', $config['optimize_empty']);
        $container->setParameter('sylius_theme.has_themes', $hasThemes);

        if ($hasThemes) {
            // Load all theme services only if themes exist or optimization is disabled
            $loader->load('services.xml');

            if ($config['assets']['enabled']) {
                $loader->load('services/integrations/assets.xml');

                if ($config['legacy_mode']) {
                    $loader->load('services/integrations/legacy_assets.xml');
                }
            }

            if ($config['templating']['enabled']) {
                $loader->load('services/integrations/templates.xml');

                if ($config['legacy_mode']) {
                    $loader->load('services/integrations/legacy_templates.xml');
                }
            }

            if ($config['translations']['enabled']) {
                $loader->load('services/integrations/translations.xml');

                if ($config['legacy_mode']) {
                    $loader->load('services/integrations/legacy_translations.xml');
                }
            }

            $this->resolveConfigurationSources($container, $config);

            $container->setAlias(ThemeContextInterface::class, $config['context']);
            $container
                ->setAlias('sylius.context.theme', ThemeContextInterface::class)
                ->setDeprecated('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.')
            ;
        } else {
            // No themes - provide minimal services
            $container->setDefinition(
                ThemeContextInterface::class,
                new \Symfony\Component\DependencyInjection\Definition(
                    'Sylius\Bundle\ThemeBundle\Context\EmptyThemeContext'
                )
            );
        }
    }

    private function hasThemes(ContainerBuilder $container, array $config): bool
    {
        // Check filesystem sources for themes
        if (isset($config['sources']['filesystem']) && $config['sources']['filesystem']['enabled']) {
            $directories = $config['sources']['filesystem']['directories'] ?? ['%kernel.project_dir%/themes'];
            $filename = $config['sources']['filesystem']['filename'] ?? 'composer.json';

            foreach ($directories as $directory) {
                $resolvedDir = $container->getParameterBag()->resolveValue($directory);

                if (!is_dir($resolvedDir)) {
                    continue;
                }

                $entries = @scandir($resolvedDir);
                if ($entries === false) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    $themeDir = $resolvedDir . '/' . $entry;
                    if (!is_dir($themeDir)) {
                        continue;
                    }

                    $configFile = $themeDir . '/' . $filename;
                    if (file_exists($configFile)) {
                        return true; // Found at least one theme
                    }
                }
            }
        }

        // Test source always has themes (programmatic)
        if (isset($config['sources']['test']) && $config['sources']['test']['enabled']) {
            return true;
        }

        return false;
    }

    public function addConfigurationSourceFactory(ConfigurationSourceFactoryInterface $configurationSourceFactory): void
    {
        $this->configurationSourceFactories[$configurationSourceFactory->getName()] = $configurationSourceFactory;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        $configuration = new Configuration($this->configurationSourceFactories);

        $container->addObjectResource($configuration);

        return $configuration;
    }

    private function resolveConfigurationSources(ContainerBuilder $container, array $config): void
    {
        $configurationProviders = [];
        foreach ($this->configurationSourceFactories as $configurationSourceFactory) {
            $sourceName = $configurationSourceFactory->getName();
            if (isset($config['sources'][$sourceName]) && $config['sources'][$sourceName]['enabled']) {
                $sourceConfig = $config['sources'][$sourceName];

                $configurationProviders[] = $configurationSourceFactory->initializeSource($container, $sourceConfig);
            }
        }

        $compositeConfigurationProvider = $container->getDefinition(ConfigurationProviderInterface::class);
        $compositeConfigurationProvider->replaceArgument(0, $configurationProviders);

        foreach ($this->configurationSourceFactories as $configurationSourceFactory) {
            $container->addObjectResource($configurationSourceFactory);
        }
    }
}
