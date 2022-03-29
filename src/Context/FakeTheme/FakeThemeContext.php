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

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class FakeThemeContext implements ThemeContextInterface
{
    public function __construct(
        private ThemeContextInterface $decoratedContext,
        private FakeThemeNameProviderInterface $fakeThemeNameProvider,
        private RequestStack $requestStack,
        private ThemeRepositoryInterface $themeRepository,
    ) {
    }

    public function getTheme(): ?ThemeInterface
    {
        $request = $this->getRequest();
        if (null === $request) {
            return $this->decoratedContext->getTheme();
        }
        $themeName = $this->fakeThemeNameProvider->getName($request);
        if (null === $themeName) {
            return $this->decoratedContext->getTheme();
        }

        return $this->themeRepository->findOneByName($themeName);
    }

    private function getRequest(): ?Request
    {
        if (\method_exists($this->requestStack, 'getMainRequest')) {
            return $this->requestStack->getMainRequest();
        }

        /** @psalm-suppress DeprecatedMethod */
        return $this->requestStack->getMasterRequest();
    }
}
