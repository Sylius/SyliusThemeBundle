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

use Sylius\Bundle\ThemeBundle\Configuration\CompositeConfigurationProvider;
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProcessorInterface;
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;
use Sylius\Bundle\ThemeBundle\Configuration\ThemeConfiguration;
use Symfony\Component\Config\Definition\Processor;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(ThemeConfiguration::class);

    $services->alias('sylius.theme.configuration', ThemeConfiguration::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(ConfigurationProcessorInterface::class, \Sylius\Bundle\ThemeBundle\Configuration\SymfonyConfigurationProcessor::class)
        ->args([
            service('sylius.theme.configuration'),
            inline_service(Processor::class),
        ]);

    $services->alias('sylius.theme.configuration.processor', ConfigurationProcessorInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');

    $services->set(ConfigurationProviderInterface::class, CompositeConfigurationProvider::class)
        ->args([[]]);

    $services->alias('sylius.theme.configuration.provider', ConfigurationProviderInterface::class)
        ->deprecate('sylius/theme-bundle', '2.0', '"%alias_id%" service is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.');
};
