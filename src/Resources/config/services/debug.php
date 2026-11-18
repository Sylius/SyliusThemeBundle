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
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ListCommand::class)
        ->args([service(ThemeRepositoryInterface::class)])
        ->tag('console.command')
    ;

    $services
        ->set(ThemeCollector::class)
        ->args([
            service(ThemeRepositoryInterface::class),
            service(ThemeContextInterface::class),
            service(ThemeHierarchyProviderInterface::class),
        ])
        ->tag('data_collector', ['template' => '@SyliusTheme/Collector/theme', 'id' => 'sylius_theme'])
    ;
};
