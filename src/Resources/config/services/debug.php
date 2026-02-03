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

use Sylius\Bundle\ThemeBundle\Collector\ThemeCollector;
use Sylius\Bundle\ThemeBundle\Command\ListCommand;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ListCommand::class)
        ->args([service('sylius.repository.theme')])
        ->tag('console.command')
    ;

    $services
        ->set(ThemeCollector::class)
        ->args([
            service('sylius.repository.theme'),
            service('sylius.context.theme'),
            service('sylius.theme.hierarchy_provider'),
        ])
        ->tag('data_collector', ['template' => '@SyliusTheme/Collector/theme', 'id' => 'sylius_theme'])
    ;
};
