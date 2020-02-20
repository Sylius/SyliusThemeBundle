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

namespace Sylius\Bundle\ThemeBundle\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class BundleResourceLocator implements ResourceLocatorInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var KernelInterface */
    private $kernel;

    public function __construct(Filesystem $filesystem, KernelInterface $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function locateResource(string $resourcePath, ThemeInterface $theme): string
    {
        $this->assertResourcePathIsValid($resourcePath);

        if (false !== strpos($resourcePath, 'Bundle/Resources/views/')) {
            // When using bundle notation, we get a path like @AcmeBundle/Resources/views/template.html.twig
            return $this->locateResourceBasedOnBundleNotation($resourcePath, $theme);
        }

        // When using namespaced Twig paths, we get a path like @Acme/template.html.twig
        return $this->locateResourceBasedOnTwigNamespace($resourcePath, $theme);
    }

    private function assertResourcePathIsValid(string $resourcePath): void
    {
        if (0 !== strpos($resourcePath, '@')) {
            throw new \InvalidArgumentException(sprintf('Bundle resource path (given "%s") should start with an "@".', $resourcePath));
        }

        if (false !== strpos($resourcePath, '..')) {
            throw new \InvalidArgumentException(sprintf('File name "%s" contains invalid characters (..).', $resourcePath));
        }
    }

    private function locateResourceBasedOnBundleNotation(string $resourcePath, ThemeInterface $theme): string
    {
        $bundleName = substr($resourcePath, 1, (int) strpos($resourcePath, '/') - 1);
        $resourceName = substr($resourcePath, (int) strpos($resourcePath, 'Resources/') + strlen('Resources/'));

        /** @var BundleInterface $bundle */
        $bundle = $this->kernel->getBundle($bundleName);

        $path = sprintf('%s/%s/%s', $theme->getPath(), $bundle->getName(), $resourceName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new ResourceNotFoundException($resourcePath, $theme);
    }

    private function locateResourceBasedOnTwigNamespace(string $resourcePath, ThemeInterface $theme): string
    {
        $twigNamespace = substr($resourcePath, 1, (int) strpos($resourcePath, '/') - 1);
        $resourceName = substr($resourcePath, (int) strpos($resourcePath, '/') + 1);

        $path = sprintf('%s/%s/views/%s', $theme->getPath(), $this->getBundleOrPluginName($twigNamespace), $resourceName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new ResourceNotFoundException($resourcePath, $theme);
    }

    private function getBundleOrPluginName(string $twigNamespace): string
    {
        if (substr($twigNamespace, -6) === 'Plugin') {
            return $twigNamespace;
        }

        return $twigNamespace . 'Bundle';
    }
}
