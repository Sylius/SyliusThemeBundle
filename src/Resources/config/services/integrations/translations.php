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
use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Sylius\Bundle\ThemeBundle\Translation\Finder\OrderingTranslationFilesFinder;
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinder;
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProvider;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProviderInterface;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\CompositeTranslatorResourceProvider;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\SymfonyTranslatorResourceProvider;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\ThemeTranslatorResourceProvider;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\TranslatorResourceProviderInterface;
use Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator;
use Sylius\Bundle\ThemeBundle\Translation\Translator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(Translator::class)
        ->decorate('translator.default', null, 256)
        ->args([
            service(TranslatorLoaderProviderInterface::class),
            service(TranslatorResourceProviderInterface::class),
            service('translator.formatter'),
            '%kernel.default_locale%',
            ['cache_dir' => '%kernel.cache_dir%/translations', 'debug' => '%kernel.debug%'],
        ]);

    $services->set(ThemeAwareTranslator::class)
        ->decorate(Translator::class, null, 256)
        ->args([
            service('Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator.inner'),
            service(ThemeContextInterface::class),
        ]);

    $services->set(TranslatorLoaderProviderInterface::class, TranslatorLoaderProvider::class)
        ->args([[]]);

    $services->set(SymfonyTranslatorResourceProvider::class)
        ->args([[]]);

    $services->set(ThemeTranslatorResourceProvider::class)
        ->args([
            service(TranslationFilesFinderInterface::class),
            service(ThemeRepositoryInterface::class),
            service(ThemeHierarchyProviderInterface::class),
        ]);

    $services->set(TranslatorResourceProviderInterface::class, CompositeTranslatorResourceProvider::class)
        ->args([[service(SymfonyTranslatorResourceProvider::class), service(ThemeTranslatorResourceProvider::class)]]);

    $services->set(TranslationFilesFinderInterface::class, TranslationFilesFinder::class)
        ->args([service(FinderFactoryInterface::class)]);

    $services->set(OrderingTranslationFilesFinder::class)
        ->decorate(TranslationFilesFinderInterface::class, null, -128)
        ->args([service('Sylius\Bundle\ThemeBundle\Translation\Finder\OrderingTranslationFilesFinder.inner')]);
};
