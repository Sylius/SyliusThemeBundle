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

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Twig\Extension\WebpackEncoreExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(WebpackEncoreExtension::class)
        ->args([
            service(ThemeContextInterface::class),
            service(ThemeHierarchyProviderInterface::class),
            service('webpack_encore.entrypoint_lookup_collection'),
            service('webpack_encore.tag_renderer'),
        ])
        ->tag('twig.extension')
    ;
};
