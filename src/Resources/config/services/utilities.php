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

use Sylius\Bundle\ThemeBundle\Factory\FinderFactory;
use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\Filesystem;
use Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FilesystemInterface::class, Filesystem::class);
    $services->set(FinderFactoryInterface::class, FinderFactory::class);
};
