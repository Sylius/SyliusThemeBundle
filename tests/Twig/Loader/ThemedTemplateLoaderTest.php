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

namespace Sylius\Bundle\ThemeBundle\Tests\Twig\Loader;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateNotFoundException;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

class ThemedTemplateLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_located_path_as_cache_key_when_template_is_found_in_active_theme(): void
    {
        $decoratedLoader = $this->createMock(TwigLoaderInterface::class);
        $templateLocator = $this->createMock(TemplateLocatorInterface::class);
        $themeContext = $this->createMock(ThemeContextInterface::class);
        $theme = $this->createMock(ThemeInterface::class);

        $themeContext->method('getTheme')->willReturn($theme);
        $templateLocator->method('locate')->willReturn('/path/to/theme-a/template.html.twig');

        $loader = new ThemedTemplateLoader($decoratedLoader, $templateLocator, $themeContext);

        $this->assertSame('/path/to/theme-a/template.html.twig', $loader->getCacheKey('template.html.twig'));
    }

    /**
     * @test
     */
    public function it_appends_theme_name_to_cache_key_when_template_is_not_in_active_theme(): void
    {
        $decoratedLoader = $this->createMock(TwigLoaderInterface::class);
        $templateLocator = $this->createMock(TemplateLocatorInterface::class);
        $themeContext = $this->createMock(ThemeContextInterface::class);
        $theme = $this->createMock(ThemeInterface::class);

        $themeContext->method('getTheme')->willReturn($theme);
        $theme->method('getName')->willReturn('sylius/theme-a');
        $templateLocator->method('locate')->willThrowException(new TemplateNotFoundException('template.html.twig', []));
        $decoratedLoader->method('getCacheKey')->willReturn('/path/to/templates/template.html.twig');

        $loader = new ThemedTemplateLoader($decoratedLoader, $templateLocator, $themeContext);

        $this->assertSame(
            '/path/to/templates/template.html.twig|sylius/theme-a',
            $loader->getCacheKey('template.html.twig'),
        );
    }

    /**
     * @test
     */
    public function it_returns_plain_cache_key_when_no_theme_is_active(): void
    {
        $decoratedLoader = $this->createMock(TwigLoaderInterface::class);
        $templateLocator = $this->createMock(TemplateLocatorInterface::class);
        $themeContext = $this->createMock(ThemeContextInterface::class);

        $themeContext->method('getTheme')->willReturn(null);
        $decoratedLoader->method('getCacheKey')->willReturn('/path/to/templates/template.html.twig');

        $loader = new ThemedTemplateLoader($decoratedLoader, $templateLocator, $themeContext);

        $this->assertSame(
            '/path/to/templates/template.html.twig',
            $loader->getCacheKey('template.html.twig'),
        );
    }

    /**
     * @test
     */
    public function it_returns_different_cache_keys_for_same_template_under_different_themes(): void
    {
        $decoratedLoader = $this->createMock(TwigLoaderInterface::class);
        $templateLocator = $this->createMock(TemplateLocatorInterface::class);
        $themeContext = $this->createMock(ThemeContextInterface::class);
        $themeA = $this->createMock(ThemeInterface::class);
        $themeB = $this->createMock(ThemeInterface::class);

        $themeA->method('getName')->willReturn('sylius/theme-a');
        $themeB->method('getName')->willReturn('sylius/theme-b');
        $templateLocator->method('locate')->willThrowException(new TemplateNotFoundException('template.html.twig', []));
        $decoratedLoader->method('getCacheKey')->willReturn('/path/to/templates/template.html.twig');

        $themeContext->method('getTheme')->willReturnOnConsecutiveCalls($themeA, $themeA, $themeB, $themeB);

        $loader = new ThemedTemplateLoader($decoratedLoader, $templateLocator, $themeContext);

        $cacheKeyA = $loader->getCacheKey('template.html.twig');
        $cacheKeyB = $loader->getCacheKey('template.html.twig');

        $this->assertNotSame($cacheKeyA, $cacheKeyB);
        $this->assertSame('/path/to/templates/template.html.twig|sylius/theme-a', $cacheKeyA);
        $this->assertSame('/path/to/templates/template.html.twig|sylius/theme-b', $cacheKeyB);
    }
}
