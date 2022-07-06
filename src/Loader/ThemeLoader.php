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

namespace Sylius\Bundle\ThemeBundle\Loader;

use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeAuthorFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeScreenshotFactoryInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeAuthor;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeScreenshot;

final class ThemeLoader implements ThemeLoaderInterface
{
    private ConfigurationProviderInterface $configurationProvider;

    private ThemeFactoryInterface $themeFactory;

    private ThemeAuthorFactoryInterface $themeAuthorFactory;

    private ThemeScreenshotFactoryInterface $themeScreenshotFactory;

    private CircularDependencyCheckerInterface $circularDependencyChecker;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        ThemeAuthorFactoryInterface $themeAuthorFactory,
        ThemeScreenshotFactoryInterface $themeScreenshotFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->themeFactory = $themeFactory;
        $this->themeAuthorFactory = $themeAuthorFactory;
        $this->themeScreenshotFactory = $themeScreenshotFactory;
        $this->circularDependencyChecker = $circularDependencyChecker;
    }

    public function load(): array
    {
        $configurations = $this->configurationProvider->getConfigurations();

        $themes = $this->hydrateThemes($configurations);

        $this->checkForCircularDependencies($themes);

        return array_values($themes);
    }

    /**
     * @return ThemeInterface[]
     */
    private function hydrateThemes(array $configurations): array
    {
        $themes = [];

        foreach ($configurations as $configuration) {
            $themes[$configuration['name']] = $this->themeFactory->create($configuration['name'], $configuration['path']);
        }

        foreach ($configurations as $configuration) {
            $theme = $themes[$configuration['name']];

            $theme->setTitle($configuration['title'] ?? null);
            $theme->setDescription($configuration['description'] ?? null);

            $parentThemes = $this->convertParentsNamesToParentsObjects($configuration['name'], $configuration['parents'], $themes);
            foreach ($parentThemes as $parentTheme) {
                $theme->addParent($parentTheme);
            }

            $themeAuthors = $this->convertAuthorsArraysToAuthorsObjects($configuration['authors']);
            foreach ($themeAuthors as $themeAuthor) {
                $theme->addAuthor($themeAuthor);
            }

            $themeScreenshots = $this->convertScreenshotsArraysToScreenshotsObjects($configuration['screenshots']);
            foreach ($themeScreenshots as $themeScreenshot) {
                $theme->addScreenshot($themeScreenshot);
            }
        }

        return $themes;
    }

    /**
     * @param array|ThemeInterface[] $themes
     */
    private function checkForCircularDependencies(array $themes): void
    {
        try {
            foreach ($themes as $theme) {
                $this->circularDependencyChecker->check($theme);
            }
        } catch (CircularDependencyFoundException $exception) {
            throw new ThemeLoadingFailedException('Circular dependency found.', 0, $exception);
        }
    }

    /**
     * @return array|ThemeInterface[]
     */
    private function convertParentsNamesToParentsObjects(string $themeName, array $parentsNames, array $existingThemes): array
    {
        return array_map(function (string $parentName) use ($themeName, $existingThemes): ThemeInterface {
            if (!isset($existingThemes[$parentName])) {
                throw new ThemeLoadingFailedException(sprintf(
                    'Unexisting theme "%s" is required by "%s".',
                    $parentName,
                    $themeName,
                ));
            }

            return $existingThemes[$parentName];
        }, $parentsNames);
    }

    /**
     * @return array|ThemeAuthor[]
     */
    private function convertAuthorsArraysToAuthorsObjects(array $authorsArrays): array
    {
        return array_map(function (array $authorArray): ThemeAuthor {
            return $this->themeAuthorFactory->createFromArray($authorArray);
        }, $authorsArrays);
    }

    /**
     * @return array|ThemeScreenshot[]
     */
    private function convertScreenshotsArraysToScreenshotsObjects(array $screenshotsArrays): array
    {
        return array_map(function (array $screenshotArray): ThemeScreenshot {
            return $this->themeScreenshotFactory->createFromArray($screenshotArray);
        }, $screenshotsArrays);
    }
}
