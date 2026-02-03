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
use Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader;
use Sylius\Bundle\ThemeBundle\Twig\Locator\ApplicationTemplateLocator;
use Sylius\Bundle\ThemeBundle\Twig\Locator\CompositeTemplateLocator;
use Sylius\Bundle\ThemeBundle\Twig\Locator\HierarchicalTemplateLocator;
use Sylius\Bundle\ThemeBundle\Twig\Locator\NamespacedTemplateLocator;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(ApplicationTemplateLocator::class)
        ->args([service('filesystem')])
        ->tag('sylius_theme.twig.template_locator');

    $services->set(NamespacedTemplateLocator::class)
        ->args([service('filesystem')])
        ->tag('sylius_theme.twig.template_locator');

    $services->set(TemplateLocatorInterface::class, CompositeTemplateLocator::class)
        ->args([tagged_iterator('sylius_theme.twig.template_locator')]);

    $services->set(HierarchicalTemplateLocator::class)
        ->decorate(TemplateLocatorInterface::class)
        ->args([
            service('Sylius\Bundle\ThemeBundle\Twig\Locator\HierarchicalTemplateLocator.inner'),
            service(ThemeHierarchyProviderInterface::class),
        ]);

    $services->set(ThemedTemplateLoader::class)
        ->decorate('twig.loader', null, 256)
        ->args([
            service('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader.inner'),
            service(TemplateLocatorInterface::class),
            service(ThemeContextInterface::class),
        ]);
};
