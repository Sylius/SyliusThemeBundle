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

namespace Sylius\Bundle\ThemeBundle\DependencyInjection\Compiler;

use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Removes theme decorators when no themes are detected.
 * This optimization prevents runtime overhead for shops not using themes.
 */
final class ConditionalDecoratorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$this->shouldOptimize($container)) {
            return;
        }

        if (!$this->hasThemes($container)) {
            $this->removeDecorators($container);
            $container->setParameter('sylius_theme.decorators_removed', true);
        }
    }

    private function shouldOptimize(ContainerBuilder $container): bool
    {
        return $container->hasParameter('sylius_theme.optimize_empty')
            && $container->getParameter('sylius_theme.optimize_empty');
    }

    private function hasThemes(ContainerBuilder $container): bool
    {
        try {
            // Get the configuration provider that scans for themes
            $loaderDefinition = $container->findDefinition(ThemeLoaderInterface::class);
            $configProviderArg = $loaderDefinition->getArgument(0);

            // Resolve the argument if it's a Reference
            $configProviderDef = $configProviderArg instanceof \Symfony\Component\DependencyInjection\Reference
                ? $container->findDefinition((string) $configProviderArg)
                : $configProviderArg;

            $providersArg = $configProviderDef->getArgument(0);

            // Resolve if it's a Reference
            $providers = $providersArg instanceof \Symfony\Component\DependencyInjection\Reference
                ? $container->findDefinition((string) $providersArg)
                : $providersArg;

            // No providers = no themes
            if (empty($providers)) {
                return false;
            }

            // Instantiate providers and check if any return theme configurations
            foreach ($providers as $providerDef) {
                if ($this->providerHasThemes($container, $providerDef)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $exception) {
            // On any error, assume themes exist (safe default)
            return true;
        }
    }

    private function providerHasThemes(ContainerBuilder $container, $providerDefinition): bool
    {
        try {
            // Get the provider class
            $providerClass = $providerDefinition->getClass();

            // For FilesystemConfigurationProvider, check if themes exist in directories
            if ($providerClass === 'Sylius\Bundle\ThemeBundle\Configuration\Filesystem\FilesystemConfigurationProvider') {
                return $this->filesystemProviderHasThemes($container, $providerDefinition);
            }

            // For TestConfigurationProvider, assume themes exist (programmatic)
            if ($providerClass === 'Sylius\Bundle\ThemeBundle\Configuration\Test\TestConfigurationProvider') {
                return true;
            }

            // Unknown provider type - assume themes might exist
            return true;
        } catch (\Exception $exception) {
            return true;
        }
    }

    private function filesystemProviderHasThemes(ContainerBuilder $container, $providerDefinition): bool
    {
        try {
            // Get the file locator from provider's first argument
            $locatorArg = $providerDefinition->getArgument(0);
            $locatorDef = $locatorArg instanceof \Symfony\Component\DependencyInjection\Reference
                ? $container->findDefinition((string) $locatorArg)
                : $locatorArg;

            $paths = $locatorDef->getArgument(1); // RecursiveFileLocator paths argument
            $filename = $providerDefinition->getArgument(2); // filename (e.g., composer.json)

            // Check each path for theme files
            foreach ($paths as $path) {
                $resolvedPath = $container->getParameterBag()->resolveValue($path);

                if (!is_dir($resolvedPath)) {
                    continue;
                }

                // Simple check: does directory have any subdirectories with the config file?
                $entries = @scandir($resolvedPath);
                if ($entries === false) {
                    continue;
                }

                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    $themeDir = $resolvedPath . '/' . $entry;
                    if (!is_dir($themeDir)) {
                        continue;
                    }

                    $configFile = $themeDir . '/' . $filename;
                    if (file_exists($configFile)) {
                        return true; // Found at least one theme
                    }
                }
            }

            return false;
        } catch (\Exception $exception) {
            return true; // On error, assume themes exist
        }
    }

    private function removeDecorators(ContainerBuilder $container): void
    {
        // Get parameters to check what's enabled
        $templatingEnabled = $container->getParameter('sylius_theme.templating_enabled');
        $translationsEnabled = $container->getParameter('sylius_theme.translations_enabled');
        $assetsEnabled = $container->getParameter('sylius_theme.assets_enabled');

        // Remove template-related services if templating is enabled
        if ($templatingEnabled) {
            $this->removeTemplateServices($container);
        }

        // Remove translation-related services if translations are enabled
        if ($translationsEnabled) {
            $this->removeTranslationServices($container);
        }

        // Remove asset-related services if assets are enabled
        if ($assetsEnabled) {
            $this->removeAssetServices($container);
        }
    }

    private function removeTemplateServices(ContainerBuilder $container): void
    {
        $services = [
            'Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader',
            'Sylius\Bundle\ThemeBundle\Twig\Locator\HierarchicalTemplateLocator',
            'Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface',
            'Sylius\Bundle\ThemeBundle\Twig\Locator\ApplicationTemplateLocator',
            'Sylius\Bundle\ThemeBundle\Twig\Locator\NamespacedTemplateLocator',
        ];

        foreach ($services as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $container->removeDefinition($serviceId);
            }
        }
    }

    private function removeTranslationServices(ContainerBuilder $container): void
    {
        $services = [
            'Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator',
            'Sylius\Bundle\ThemeBundle\Translation\Translator',
            'Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProviderInterface',
            'Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\SymfonyTranslatorResourceProvider',
            'Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\ThemeTranslatorResourceProvider',
            'Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\TranslatorResourceProviderInterface',
            'Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface',
            'Sylius\Bundle\ThemeBundle\Translation\Finder\OrderingTranslationFilesFinder',
        ];

        foreach ($services as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $container->removeDefinition($serviceId);
            }
        }
    }

    private function removeAssetServices(ContainerBuilder $container): void
    {
        // Restore the original Symfony assets.path_package definition
        // instead of the theme-aware one
        if ($container->hasDefinition('assets.path_package')) {
            $definition = new \Symfony\Component\DependencyInjection\Definition(
                'Symfony\Component\Asset\PathPackage'
            );
            $definition->setAbstract(true);
            $definition->addArgument(null); // base path
            $definition->addArgument(null); // version strategy
            $definition->addArgument(new \Symfony\Component\DependencyInjection\Reference('assets.context'));

            $container->setDefinition('assets.path_package', $definition);
        }

        $services = [
            'Sylius\Bundle\ThemeBundle\Asset\PathResolver',
            'Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface',
            'Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstallerInterface',
            'Sylius\Bundle\ThemeBundle\Asset\Installer\OutputAwareAssetsInstaller',
            'Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface',
            'Sylius\Bundle\ThemeBundle\Command\AssetsInstallCommand',
        ];

        foreach ($services as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $container->removeDefinition($serviceId);
            }
        }
    }
}
