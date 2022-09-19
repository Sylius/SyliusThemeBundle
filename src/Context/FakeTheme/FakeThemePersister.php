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

namespace Sylius\Bundle\ThemeBundle\Context\FakeTheme;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FakeThemePersister
{
    public function __construct(private FakeThemeNameProviderInterface $fakeThemeNameProvider)
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (HttpKernelInterface::SUB_REQUEST === $event->getRequestType()) {
            return;
        }

        $fakeThemeName = $this->fakeThemeNameProvider->getName($event->getRequest());
        if (null === $fakeThemeName) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->setCookie(new Cookie(FakeThemeNameProviderInterface::PARAMETER_NAME, $fakeThemeName));
    }
}
