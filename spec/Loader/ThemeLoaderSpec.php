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

namespace spec\Sylius\Bundle\ThemeBundle\Loader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeAuthorFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeFactoryInterface;
use Sylius\Bundle\ThemeBundle\Factory\ThemeScreenshotFactoryInterface;
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyCheckerInterface;
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyFoundException;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoadingFailedException;
use Sylius\Bundle\ThemeBundle\Model\Theme;
use Sylius\Bundle\ThemeBundle\Model\ThemeAuthor;
use Sylius\Bundle\ThemeBundle\Model\ThemeScreenshot;

final class ThemeLoaderSpec extends ObjectBehavior
{
    function let(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        ThemeAuthorFactoryInterface $themeAuthorFactory,
        ThemeScreenshotFactoryInterface $themeScreenshotFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $this->beConstructedWith(
            $configurationProvider,
            $themeFactory,
            $themeAuthorFactory,
            $themeScreenshotFactory,
            $circularDependencyChecker,
        );
    }

    function it_implements_theme_loader_interface(): void
    {
        $this->shouldImplement(ThemeLoaderInterface::class);
    }

    function it_loads_a_single_theme(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $theme = new Theme('first/theme', '/theme/path');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/theme/path',
                'parents' => [],
                'authors' => [],
                'screenshots' => [],
            ],
        ]);

        $themeFactory->create('first/theme', '/theme/path')->willReturn($theme);

        $circularDependencyChecker->check($theme)->shouldBeCalled();

        $expectedTheme = new Theme('first/theme', '/theme/path');
        $this->load()->shouldBeLike([$expectedTheme]);
    }

    function it_loads_a_theme_with_author(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        ThemeAuthorFactoryInterface $themeAuthorFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $theme = new Theme('first/theme', '/theme/path');

        $themeAuthor = new ThemeAuthor();
        $themeAuthor->setName('Richard Rynkowsky');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/theme/path',
                'parents' => [],
                'authors' => [['name' => 'Richard Rynkowsky']],
                'screenshots' => [],
            ],
        ]);

        $themeFactory->create('first/theme', '/theme/path')->willReturn($theme);
        $themeAuthorFactory->createFromArray(['name' => 'Richard Rynkowsky'])->willReturn($themeAuthor);

        $circularDependencyChecker->check($theme)->shouldBeCalled();

        $expectedTheme = new Theme('first/theme', '/theme/path');
        $expectedTheme->addAuthor($themeAuthor);

        $this->load()->shouldBeLike([$expectedTheme]);
    }

    function it_loads_a_theme_with_screenshot(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        ThemeScreenshotFactoryInterface $themeScreenshotFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $theme = new Theme('first/theme', '/theme/path');

        $themeScreenshot = new ThemeScreenshot('screenshot/omg.jpg');
        $themeScreenshot->setTitle('Title');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/theme/path',
                'parents' => [],
                'authors' => [],
                'screenshots' => [
                    ['path' => 'screenshot/omg.jpg', 'title' => 'Title'],
                ],
            ],
        ]);

        $themeFactory->create('first/theme', '/theme/path')->willReturn($theme);
        $themeScreenshotFactory->createFromArray(['path' => 'screenshot/omg.jpg', 'title' => 'Title'])->willReturn($themeScreenshot);

        $circularDependencyChecker->check($theme)->shouldBeCalled();

        $expectedTheme = new Theme('first/theme', '/theme/path');
        $expectedTheme->addScreenshot($themeScreenshot);

        $this->load()->shouldBeLike([$expectedTheme]);
    }

    function it_loads_a_theme_with_its_dependency(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $firstTheme = new Theme('first/theme', '/first/theme/path');
        $secondTheme = new Theme('second/theme', '/second/theme/path');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/first/theme/path',
                'parents' => ['second/theme'],
                'authors' => [],
                'screenshots' => [],
            ],
            [
                'name' => 'second/theme',
                'path' => '/second/theme/path',
                'parents' => [],
                'authors' => [],
                'screenshots' => [],
            ],
        ]);

        $themeFactory->create('first/theme', '/first/theme/path')->willReturn($firstTheme);
        $themeFactory->create('second/theme', '/second/theme/path')->willReturn($secondTheme);

        $circularDependencyChecker->check($firstTheme)->shouldBeCalled();
        $circularDependencyChecker->check($secondTheme)->shouldBeCalled();

        $expectedFirstTheme = new Theme('first/theme', '/first/theme/path');
        $expectedSecondTheme = new Theme('second/theme', '/second/theme/path');

        $expectedFirstTheme->addParent($expectedSecondTheme);

        $this->load()->shouldBeLike([$expectedFirstTheme, $expectedSecondTheme]);
    }

    function it_throws_an_exception_if_requires_not_existing_dependency(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
    ): void {
        $firstTheme = new Theme('first/theme', '/theme/path');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/theme/path',
                'parents' => ['second/theme'],
                'authors' => [],
                'screenshots' => [],
            ],
        ]);

        $themeFactory->create('first/theme', '/theme/path')->willReturn($firstTheme);

        $this
            ->shouldThrow(new ThemeLoadingFailedException('Unexisting theme "second/theme" is required by "first/theme".'))
            ->during('load')
        ;
    }

    function it_throws_an_exception_if_there_is_a_circular_dependency_found(
        ConfigurationProviderInterface $configurationProvider,
        ThemeFactoryInterface $themeFactory,
        CircularDependencyCheckerInterface $circularDependencyChecker,
    ): void {
        $firstTheme = new Theme('first/theme', '/first/theme/path');
        $secondTheme = new Theme('second/theme', '/second/theme/path');

        $configurationProvider->getConfigurations()->willReturn([
            [
                'name' => 'first/theme',
                'path' => '/first/theme/path',
                'parents' => ['second/theme'],
                'authors' => [],
                'screenshots' => [],
            ],
            [
                'name' => 'second/theme',
                'path' => '/second/theme/path',
                'parents' => ['first/theme'],
                'authors' => [],
                'screenshots' => [],
            ],
        ]);

        $themeFactory->create('first/theme', '/first/theme/path')->willReturn($firstTheme);
        $themeFactory->create('second/theme', '/second/theme/path')->willReturn($secondTheme);

        $circularDependencyChecker->check(Argument::cetera())->willThrow(CircularDependencyFoundException::class);

        $this
            ->shouldThrow(new ThemeLoadingFailedException('Circular dependency found.'))
            ->during('load')
        ;
    }
}
