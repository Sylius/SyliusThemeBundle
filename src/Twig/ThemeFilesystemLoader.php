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

namespace Sylius\Bundle\ThemeBundle\Twig;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReference;
use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class ThemeFilesystemLoader implements LoaderInterface, ExistsLoaderInterface
{
    /** @var LoaderInterface */
    private $decoratedLoader;

    /** @var FileLocatorInterface */
    private $templateLocator;

    /** @var TemplateNameParserInterface */
    private $templateNameParser;

    /** @var string[] */
    private $cache = [];

    public function __construct(
        LoaderInterface $decoratedLoader,
        FileLocatorInterface $templateLocator,
        TemplateNameParserInterface $templateNameParser
    ) {
        $this->decoratedLoader = $decoratedLoader;
        $this->templateLocator = $templateLocator;
        $this->templateNameParser = $templateNameParser;
    }

    /**
     * @param string|TemplateReference $name
     */
    public function getSourceContext($name): Source
    {
        try {
            $path = $this->findTemplate($name);

            return new Source((string) file_get_contents($path), (string) $name, $path);
        } catch (\Exception $exception) {
            /** @psalm-suppress ImplicitToStringCast */
            return $this->decoratedLoader->getSourceContext($name);
        }
    }

    /**
     * @param string|TemplateReference $name
     */
    public function getCacheKey($name): string
    {
        try {
            return $this->findTemplate($name);
        } catch (\Exception $exception) {
            /** @psalm-suppress ImplicitToStringCast */
            return $this->decoratedLoader->getCacheKey($name);
        }
    }

    /**
     * @param string|TemplateReference $name
     * @param int $time
     */
    public function isFresh($name, $time): bool
    {
        try {
            return filemtime($this->findTemplate($name)) <= $time;
        } catch (\Exception $exception) {
            /** @psalm-suppress ImplicitToStringCast */
            return $this->decoratedLoader->isFresh($name, $time);
        }
    }

    /**
     * @param string|TemplateReference $name
     */
    public function exists($name): bool
    {
        try {
            return stat($this->findTemplate($name)) !== false;
        } catch (\Exception $exception) {
            /** @psalm-suppress ImplicitToStringCast */
            return $this->decoratedLoader->exists($name);
        }
    }

    /**
     * @param string|TemplateReference $logicalName
     */
    private function findTemplate($logicalName): string
    {
        $logicalName = (string) $logicalName;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $template = $this->templateNameParser->parse($logicalName);

        /**
         * @var string
         * @psalm-suppress ImplicitToStringCast
         */
        $file = $this->templateLocator->locate($template);

        return $this->cache[$logicalName] = $file;
    }
}
