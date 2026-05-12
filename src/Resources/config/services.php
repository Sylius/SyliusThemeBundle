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

use Sylius\Bundle\ThemeBundle\Context\EmptyThemeContext;
use Sylius\Bundle\ThemeBundle\Context\SettableThemeContext;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProvider;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;
use Sylius\Bundle\ThemeBundle\Repository\InMemoryThemeRepository;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $container->import('services/*.php');

    $services = $container->services();

    $services->set(ThemeContextInterface::class, EmptyThemeContext::class);

    $services->set(SettableThemeContext::class)->args([service(ThemeHierarchyProviderInterface::class)]);
    $services
        ->alias('sylius.theme.context.settable', SettableThemeContext::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.')
    ;

    $services
        ->set(ThemeRepositoryInterface::class, InMemoryThemeRepository::class)
        ->args([
            service(ThemeLoaderInterface::class),
        ])
    ;
    $services
        ->alias('sylius.repository.theme', ThemeRepositoryInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.')
    ;

    $services->set(ThemeHierarchyProviderInterface::class, ThemeHierarchyProvider::class);
    $services
        ->alias('sylius.theme.hierarchy_provider', ThemeHierarchyProviderInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.')
    ;
};
