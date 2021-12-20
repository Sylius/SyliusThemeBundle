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

final class AssetsProvider implements AssetsProviderInterface
{
    private KernelInterface $kernel;

    private ThemeHierarchyProviderInterface $themeHierarchyProvider;

    public function __construct(KernelInterface $kernel, ThemeHierarchyProviderInterface $themeHierarchyProvider)
    {
        $this->kernel = $kernel;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
    }

    public function provideDirectoriesForTheme(ThemeInterface $rootTheme): iterable
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            yield from $this->provideDirectoriesForBundle($bundle);
        }

        $themes = array_reverse($this->themeHierarchyProvider->getThemeHierarchy($rootTheme));

        foreach ($themes as $theme) {
            yield $theme->getPath() . '/public' => '/';
        }
    }

    public function provideDirectoriesForBundle(BundleInterface $bundle): iterable
    {
        yield $bundle->getPath() . '/Resources/public' => '/bundles/' . $this->getPublicBundleName($bundle);
        yield $bundle->getPath() . '/public' => '/bundles/' . $this->getPublicBundleName($bundle);
    }

    private function getPublicBundleName(BundleInterface $bundle): string
    {
        return (string) preg_replace('/bundle$/', '', strtolower($bundle->getName()));
    }
}
