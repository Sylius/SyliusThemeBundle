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

use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\Asset\Installer\LegacyAssetsProvider;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(LegacyAssetsProvider::class)
        ->decorate(AssetsProviderInterface::class)
        ->args([
            service('Sylius\Bundle\ThemeBundle\Asset\Installer\LegacyAssetsProvider.inner'),
            service('kernel'),
            service(ThemeHierarchyProviderInterface::class),
        ]);
};
