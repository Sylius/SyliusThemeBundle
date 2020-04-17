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

    public function locateResource(string $template, ThemeInterface $theme): string
    {
        $this->assertResourcePathIsValid($template);

        if (false !== strpos($template, 'Resources/views/')) {
            // When using bundle notation, we get a path like @AcmeBundle/Resources/views/template.html.twig
            return $this->locateResourceBasedOnBundleNotation($template, $theme);
        }

        // When using namespaced Twig paths, we get a path like @Acme/template.html.twig
        return $this->locateResourceBasedOnTwigNamespace($template, $theme);
    }

    private function assertResourcePathIsValid(string $template): void
    {
        if (0 !== strpos($template, '@')) {
            throw new \InvalidArgumentException(sprintf('Bundle resource path (given "%s") should start with an "@".', $template));
        }

        if (false !== strpos($template, '..')) {
            throw new \InvalidArgumentException(sprintf('File name "%s" contains invalid characters (..).', $template));
        }
    }

    private function locateResourceBasedOnBundleNotation(string $template, ThemeInterface $theme): string
    {
        $bundleName = substr($template, 1, (int) strpos($template, '/') - 1);
        $resourceName = substr($template, (int) strpos($template, 'Resources/views/') + strlen('Resources/views/'));

        $bundle = $this->kernel->getBundle($bundleName);

        $path = sprintf('%s/templates/bundles/%s/%s', $theme->getPath(), $bundle->getName(), $resourceName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new ResourceNotFoundException($template, [$theme]);
    }

    private function locateResourceBasedOnTwigNamespace(string $template, ThemeInterface $theme): string
    {
        $twigNamespace = substr($template, 1, (int) strpos($template, '/') - 1);
        $resourceName = substr($template, (int) strpos($template, '/') + 1);

        $path = sprintf('%s/templates/bundles/%s/%s', $theme->getPath(), $this->getBundleOrPluginName($twigNamespace), $resourceName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new ResourceNotFoundException($template, [$theme]);
    }

    private function getBundleOrPluginName(string $twigNamespace): string
    {
        if (substr($twigNamespace, -6) === 'Plugin') {
            return $twigNamespace;
        }

        return $twigNamespace . 'Bundle';
    }
}
