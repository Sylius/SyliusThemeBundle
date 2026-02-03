<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstaller;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstallerInterface;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProvider;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\Asset\Installer\OutputAwareAssetsInstaller;
use Sylius\Bundle\ThemeBundle\Asset\Package\PathPackage;
use Sylius\Bundle\ThemeBundle\Asset\PathResolver;
use Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface;
use Sylius\Bundle\ThemeBundle\Command\AssetsInstallCommand;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(AssetsInstallCommand::class)
        ->args([
            service(AssetsInstallerInterface::class),
            '%kernel.project_dir%',
        ])
        ->tag('console.command')
    ;

    $services
        ->set(AssetsInstallerInterface::class, AssetsInstaller::class)
        ->args([
            service('filesystem'),
            service('kernel'),
            service(ThemeRepositoryInterface::class),
            service(PathResolverInterface::class),
            service(AssetsProviderInterface::class),
        ])
    ;

    $services
        ->set(OutputAwareAssetsInstaller::class)
        ->decorate(AssetsInstallerInterface::class, null, 256)
        ->args([
            service('.inner'),
        ])
    ;

    $services
        ->set(PathResolverInterface::class, PathResolver::class)
        ->args([
            service(AssetsProviderInterface::class),
            service(FilesystemInterface::class),
        ])
    ;

    $services
        ->set(AssetsProviderInterface::class, AssetsProvider::class)
        ->args([
            service('kernel'),
            service(ThemeHierarchyProviderInterface::class),
        ])
    ;

    // Overridden services
    $services
        ->set('assets.path_package', PathPackage::class)
        ->args([
            abstract_arg('base path'),
            abstract_arg('version strategy'),
            service(ThemeContextInterface::class),
            service(PathResolverInterface::class),
            service('assets.context'),
        ])
        ->abstract()
    ;
};
