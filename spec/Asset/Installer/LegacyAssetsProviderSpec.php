<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\Bundle\ThemeBundle\Asset\Installer;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class LegacyAssetsProviderSpec extends ObjectBehavior
{
    function let(
        AssetsProviderInterface $assetsProvider,
        KernelInterface $kernel,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        BundleInterface $acmeBundle,
        ThemeInterface $childTheme,
        ThemeInterface $parentTheme,
    ) {
        $kernel->getBundles()->willReturn([$acmeBundle]);

        $acmeBundle->getPath()->willReturn('/src/bundle/AcmeBundle');
        $acmeBundle->getName()->willReturn('AcmeBundle');

        $childTheme->getPath()->willReturn('/src/theme/child');
        $parentTheme->getPath()->willReturn('/src/theme/parent');

        $themeHierarchyProvider->getThemeHierarchy($childTheme)->willReturn([$childTheme, $parentTheme]);

        $this->beConstructedWith($assetsProvider, $kernel, $themeHierarchyProvider);
    }

    function it_should_trigger_deprecated_warning_during_instantiation(): void
    {
        $this->shouldTrigger(\E_USER_DEPRECATED)->duringInstantiation();
    }

    function it_is_an_assets_provider(): void
    {
        $this->shouldImplement(AssetsProviderInterface::class);
    }

    function it_returns_map_for_bundle(AssetsProviderInterface $assetsProvider, BundleInterface $acmeBundle): void
    {
        $assetsProvider->provideDirectoriesForBundle($acmeBundle)->willYield([
            '/target' => '/origin',
        ]);

        $this->provideDirectoriesForBundle($acmeBundle)->shouldYield([
            '/target' => '/origin',
        ]);
    }

    function it_returns_map_for_theme(AssetsProviderInterface $assetsProvider, ThemeInterface $childTheme): void
    {
        $assetsProvider->provideDirectoriesForTheme($childTheme)->willYield([
            '/target' => '/origin',
        ]);

        $this->provideDirectoriesForTheme($childTheme)->shouldYield([
            '/src/theme/parent/AcmeBundle/public' => '/bundles/acme',
            '/src/theme/child/AcmeBundle/public' => '/bundles/acme',
            '/target' => '/origin',
        ]);
    }
}
