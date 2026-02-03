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

use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\Translation\Finder\LegacyTranslationFilesFinder;
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(LegacyTranslationFilesFinder::class)
        ->decorate(TranslationFilesFinderInterface::class, null, 128)
        ->args([service(FinderFactoryInterface::class)]);
};
