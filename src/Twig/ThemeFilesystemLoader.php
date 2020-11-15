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
use Twig\Error\LoaderError;
use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class ThemeFilesystemLoader implements LoaderInterface, ExistsLoaderInterface
{
    /** @var FileLocatorInterface */
    private $templateLocator;

    /** @var TemplateNameParserInterface */
    private $templateNameParser;

    /** @var string[] */
    private $cache = [];

    /** @var string[] */
    private $errorCache = [];

    public function __construct(
        FileLocatorInterface $templateLocator,
        TemplateNameParserInterface $templateNameParser
    ) {
        $this->templateLocator = $templateLocator;
        $this->templateNameParser = $templateNameParser;
    }

    /**
     * @param string|TemplateReference $name
     */
    public function getSourceContext($name): Source
    {
        $path = $this->findTemplate($name);

        return new Source((string) file_get_contents($path), (string) $name, $path);
    }

    /**
     * @param string|TemplateReference $name
     */
    public function getCacheKey($name): string
    {
        return $this->findTemplate($name);
    }

    /**
     * @param string|TemplateReference $name
     *
     * @throws LoaderError
     */
    public function isFresh($name, $time): bool
    {
        if (!$path = $this->findTemplate($name)) {
            return false;
        }

        return filemtime($path) < $time;
    }

    /**
     * @param string|TemplateReference $name
     */
    public function exists($name): bool
    {
        try {
            return stat($this->findTemplate($name)) !== false;
        } catch (LoaderError $e) {
            return false;
        }
    }

    /**
     * @param string|TemplateReference $logicalName
     *
     * @throws LoaderError
     */
    private function findTemplate($logicalName): string
    {
        $logicalName = (string) $logicalName;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        if (isset($this->errorCache[$logicalName])) {
            throw new LoaderError($this->errorCache[$logicalName]);
        }

        try {
            $template = $this->templateNameParser->parse($logicalName);

            /**
             * @var string
             * @psalm-suppress ImplicitToStringCast
             */
            $file = $this->templateLocator->locate($template);

            return $this->cache[$logicalName] = $file;
        } catch (\Exception $e) {
            $this->errorCache[$logicalName] = $e->getMessage();

            throw new LoaderError($e->getMessage());
        }
    }
}
