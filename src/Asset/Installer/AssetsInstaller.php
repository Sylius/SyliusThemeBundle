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

use Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class AssetsInstaller implements AssetsInstallerInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var KernelInterface */
    private $kernel;

    /** @var ThemeRepositoryInterface */
    private $themeRepository;

    /** @var ThemeHierarchyProviderInterface */
    private $themeHierarchyProvider;

    /** @var PathResolverInterface */
    private $pathResolver;

    public function __construct(
        Filesystem $filesystem,
        KernelInterface $kernel,
        ThemeRepositoryInterface $themeRepository,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        PathResolverInterface $pathResolver
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->themeRepository = $themeRepository;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
        $this->pathResolver = $pathResolver;
    }

    public function installAssets(string $targetDir, int $symlinkMask): int
    {
        // Create the bundles directory otherwise symlink will fail.
        $targetDir = rtrim($targetDir, '/') . '/bundles/';
        $this->filesystem->mkdir($targetDir);

        $effectiveSymlinkMask = $symlinkMask;
        foreach ($this->kernel->getBundles() as $bundle) {
            $effectiveSymlinkMask = min($effectiveSymlinkMask, $this->installBundleAssets($bundle, $targetDir, $symlinkMask));
        }

        foreach ($this->themeRepository->findAll() as $theme) {
            $effectiveSymlinkMask = min($effectiveSymlinkMask, $this->installThemeAssets($theme, $targetDir, $symlinkMask));
        }

        return $effectiveSymlinkMask;
    }

    public function installBundleAssets(BundleInterface $bundle, string $targetDir, int $symlinkMask): int
    {
        $effectiveSymlinkMask = $symlinkMask;

        $targetDir .= preg_replace('/bundle$/', '', strtolower($bundle->getName()));

        $this->filesystem->remove($targetDir);

        $originDir = $bundle->getPath() . '/Resources/public';

        if (is_dir($originDir)) {
            $effectiveSymlinkMask = min($effectiveSymlinkMask, $this->doInstallAssets($originDir, $targetDir, $symlinkMask));
        }

        return $effectiveSymlinkMask;
    }

    public function installThemeAssets(ThemeInterface $theme, string $targetDir, int $symlinkMask): int
    {
        $effectiveSymlinkMask = $symlinkMask;

        $targetDir = $this->pathResolver->resolve($targetDir, $theme);

        $this->filesystem->mkdir($targetDir);

        foreach ($this->kernel->getBundles() as $bundle) {
            $effectiveSymlinkMask = min($effectiveSymlinkMask, $this->installBundleAssets($bundle, $targetDir, $symlinkMask));
        }

        $themes = array_reverse($this->themeHierarchyProvider->getThemeHierarchy($theme));

        foreach ($themes as $theme) {
            $originDir = $theme->getPath() . '/public';

            if (!is_dir($originDir)) {
                continue;
            }

            $effectiveSymlinkMask = min($effectiveSymlinkMask, $this->doInstallAssets($originDir, $targetDir, $symlinkMask));
        }

        return $effectiveSymlinkMask;
    }

    private function doInstallAssets(string $originDir, string $targetDir, int $symlinkMask): int
    {
        $effectiveSymlinkMask = $symlinkMask;

        $finder = new Finder();
        $finder->sortByName()->ignoreDotFiles(false)->in($originDir);

        foreach ($finder as $originFile) {
            $targetFile = rtrim($targetDir, '/') . '/' . $originFile->getRelativePathname();

            $this->filesystem->mkdir(dirname($targetFile));

            $effectiveSymlinkMask = min(
                $effectiveSymlinkMask,
                $this->installAsset($originFile->getPathname(), $targetFile, $symlinkMask)
            );
        }

        return $effectiveSymlinkMask;
    }

    private function installAsset(string $origin, string $target, int $symlinkMask): int
    {
        if (is_file($target)) {
            $this->filesystem->remove($target);
        }

        if (AssetsInstallerInterface::RELATIVE_SYMLINK === $symlinkMask) {
            try {
                $targetDirname = (string) realpath(is_dir($target) ? $target : dirname($target));
                $relativeOrigin = rtrim($this->filesystem->makePathRelative($origin, $targetDirname), '/');

                $this->doInstallAsset($relativeOrigin, $target, true);

                return AssetsInstallerInterface::RELATIVE_SYMLINK;
            } catch (IOException $exception) {
                // Do nothing, trying to create non-relative symlinks later.
            }
        }

        if (AssetsInstallerInterface::HARD_COPY !== $symlinkMask) {
            try {
                $this->doInstallAsset($origin, $target, true);

                return AssetsInstallerInterface::SYMLINK;
            } catch (IOException $exception) {
                // Do nothing, hard copy later.
            }
        }

        $this->doInstallAsset($origin, $target, false);

        return AssetsInstallerInterface::HARD_COPY;
    }

    /**
     * @throws IOException When failed to make symbolic link, if requested.
     */
    private function doInstallAsset(string $origin, string $target, bool $symlink): void
    {
        if ($symlink) {
            $this->doSymlinkAsset($origin, $target);

            return;
        }

        $this->doCopyAsset($origin, $target);
    }

    /**
     * @throws IOException If symbolic link is broken
     */
    private function doSymlinkAsset(string $origin, string $target): void
    {
        $this->filesystem->symlink($origin, $target);

        if (!file_exists($target)) {
            throw new IOException('Symbolic link is broken');
        }
    }

    private function doCopyAsset(string $origin, string $target): void
    {
        if (is_dir($origin)) {
            $this->filesystem->mkdir($target, 0777);
            $this->filesystem->mirror($origin, $target, Finder::create()->ignoreDotFiles(false)->in($origin));

            return;
        }

        $this->filesystem->copy($origin, $target);
    }
}
