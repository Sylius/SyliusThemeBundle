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
use Prophecy\Argument;
use Sylius\Bundle\ThemeBundle\Context\FakeTheme\FakeThemeNameProviderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FakeThemePersisterSpec extends ObjectBehavior
{
    public function let(FakeThemeNameProviderInterface $fakeThemeNameProvider): void
    {
        $this->beConstructedWith($fakeThemeNameProvider);
    }

    function it_applies_only_to_main_requests(
        HttpKernelInterface $kernel,
        Request $request,
        Response $response
    ): void {
        $this->onKernelResponse(new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::SUB_REQUEST,
            $response->getWrappedObject()
        ));
    }

    function it_applies_only_for_request_with_fake_theme_name(
        FakeThemeNameProviderInterface $fakeThemeNameProvider,
        HttpKernelInterface $kernel,
        Request $request,
        Response $response
    ): void {
        $fakeThemeNameProvider->getName($request)->willReturn(null);

        $this->onKernelResponse(new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        ));
    }

    function it_persists_fake_channel_codes_in_a_cookie(
        FakeThemeNameProviderInterface $fakeThemeNameProvider,
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $responseHeaderBag
    ): void {
        $fakeThemeNameProvider->getName($request)->willReturn('fake_theme_name');

        $response->headers = $responseHeaderBag;
        $responseHeaderBag
            ->setCookie(Argument::that(static fn(Cookie $cookie): bool => $cookie->getName() === '_theme_name' && $cookie->getValue() === 'fake_theme_name'))
            ->shouldBeCalled()
        ;

        $this->onKernelResponse(new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        ));
    }
}
