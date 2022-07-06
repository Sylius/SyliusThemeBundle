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
