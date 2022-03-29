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

namespace spec\Sylius\Bundle\ThemeBundle\Context\FakeTheme;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Context\FakeTheme\FakeThemeNameProviderInterface;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Model\Theme;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class FakeThemeContextSpec extends ObjectBehavior
{
    public function let(
        ThemeContextInterface $decoratedContext,
        FakeThemeNameProviderInterface $fakeThemeNameProvider,
        RequestStack $requestStack,
        ThemeRepositoryInterface $themeRepository
    ): void {
        $this->beConstructedWith($decoratedContext, $fakeThemeNameProvider, $requestStack, $themeRepository);
    }

    public function it_implements_theme_context(): void
    {
        $this->shouldImplement(ThemeContextInterface::class);
    }

    public function it_falls_back_to_decorated_context_if_request_is_null(ThemeContextInterface $decoratedContext): void
    {
        $this->getTheme();

        $decoratedContext->getTheme()->shouldHaveBeenCalled();
    }

    public function it_falls_back_to_decorated_if_theme_name_is_null(
        ThemeContextInterface $decoratedContext,
        RequestStack $requestStack,
    ): void {
        $request = new Request();
        if (method_exists(RequestStack::class, 'getMainRequest')) {
            $requestStack->getMainRequest()->willReturn($request);
        } else {
            $requestStack->getMasterRequest()->willReturn($request);
        }

        $this->getTheme();

        $decoratedContext->getTheme()->shouldHaveBeenCalled();
    }

    public function it_returns_null_if_theme_does_not_exist(
        RequestStack $requestStack,
        FakeThemeNameProviderInterface $fakeThemeNameProvider
    ): void {
        $request = new Request();
        if (method_exists(RequestStack::class, 'getMainRequest')) {
            $requestStack->getMainRequest()->willReturn($request);
        } else {
            $requestStack->getMasterRequest()->willReturn($request);
        }
        $fakeThemeNameProvider->getName($request)->willReturn('fake/theme');

        $this->getTheme()->shouldBeNull();
    }

    public function it_returns_theme_if_it_exists(
        RequestStack $requestStack,
        FakeThemeNameProviderInterface $fakeThemeNameProvider,
        ThemeRepositoryInterface $themeRepository,
    ): void {
        $theme = new Theme('fake/theme', 'fake/path');
        $request = new Request();
        if (method_exists(RequestStack::class, 'getMainRequest')) {
            $requestStack->getMainRequest()->willReturn($request);
        } else {
            $requestStack->getMasterRequest()->willReturn($request);
        }
        $fakeThemeNameProvider->getName($request)->willReturn('fake/theme');
        $themeRepository->findOneByName('fake/theme')->willReturn($theme);

        $this->getTheme()->shouldReturn($theme);
    }
}
