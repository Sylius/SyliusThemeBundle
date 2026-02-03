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

use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeAuthorFactory;
use Sylius\Bundle\ThemeBundle\Factory\ThemeAuthorFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeFactory;
use Sylius\Bundle\ThemeBundle\Factory\ThemeFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeScreenshotFactory;
use Sylius\Bundle\ThemeBundle\Factory\ThemeScreenshotFactoryInterface;
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyChecker;
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyCheckerInterface;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoader;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(ThemeFactoryInterface::class, ThemeFactory::class);

    $services->alias('sylius.factory.theme', ThemeFactoryInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(ThemeAuthorFactoryInterface::class, ThemeAuthorFactory::class);

    $services->alias('sylius.factory.theme_author', ThemeAuthorFactoryInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(ThemeScreenshotFactoryInterface::class, ThemeScreenshotFactory::class);

    $services->alias('sylius.factory.theme_screenshot', ThemeScreenshotFactoryInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(CircularDependencyCheckerInterface::class, CircularDependencyChecker::class);

    $services->alias('sylius.theme.circular_dependency_checker', CircularDependencyCheckerInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(ThemeLoaderInterface::class, ThemeLoader::class)
        ->args([
            service(ConfigurationProviderInterface::class),
            service(ThemeFactoryInterface::class),
            service(ThemeAuthorFactoryInterface::class),
            service(ThemeScreenshotFactoryInterface::class),
            service(CircularDependencyCheckerInterface::class),
        ]);

    $services->alias('sylius.theme.loader', ThemeLoaderInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');
};
