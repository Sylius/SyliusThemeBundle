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

use Sylius\Bundle\ThemeBundle\Twig\Locator\LegacyApplicationTemplateLocator;
use Sylius\Bundle\ThemeBundle\Twig\Locator\LegacyNamespacedTemplateLocator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(LegacyApplicationTemplateLocator::class)
        ->args([
            service('filesystem'),
        ])
        ->tag('sylius_theme.twig.template_locator', ['priority' => -64])
    ;

    $services
        ->set(LegacyNamespacedTemplateLocator::class)
        ->args([
            service('filesystem'),
        ])
        ->tag('sylius_theme.twig.template_locator', ['priority' => -64])
    ;
};
