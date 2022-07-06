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

namespace Sylius\Bundle\ThemeBundle\Asset\Installer;

use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @deprecated Deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.
 */
final class LegacyAssetsProvider implements AssetsProviderInterface
{
    private AssetsProviderInterface $assetsProvider;

    private KernelInterface $kernel;

    private ThemeHierarchyProviderInterface $themeHierarchyProvider;

    public function __construct(AssetsProviderInterface $assetsProvider, KernelInterface $kernel, ThemeHierarchyProviderInterface $themeHierarchyProvider)
    {
        @trigger_error(sprintf(
            '"%s" is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.',
            self::class,
        ), \E_USER_DEPRECATED);

        $this->assetsProvider = $assetsProvider;
        $this->kernel = $kernel;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
    }

    public function provideDirectoriesForTheme(ThemeInterface $rootTheme): iterable
    {
        $themes = array_reverse($this->themeHierarchyProvider->getThemeHierarchy($rootTheme));

        foreach ($themes as $theme) {
            foreach ($this->kernel->getBundles() as $bundle) {
                yield $theme->getPath() . '/' . $bundle->getName() . '/public' => '/bundles/' . $this->getPublicBundleName($bundle);
            }
        }

        yield from $this->assetsProvider->provideDirectoriesForTheme($rootTheme);
    }

    public function provideDirectoriesForBundle(BundleInterface $bundle): iterable
    {
        yield from $this->assetsProvider->provideDirectoriesForBundle($bundle);
    }

    private function getPublicBundleName(BundleInterface $bundle): string
    {
        return (string) preg_replace('/bundle$/', '', strtolower($bundle->getName()));
    }
}
