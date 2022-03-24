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

use Symfony\Component\HttpFoundation\Request;

final class FakeThemeNameProvider implements FakeThemeNameProviderInterface
{
    public function getName(Request $request): ?string
    {
        $themeName = $request->query->get(self::PARAMETER_NAME);
        if (\is_string($themeName) && '' !== $themeName) {
            return $themeName;
        }

        $themeName = $request->cookies->get(self::PARAMETER_NAME);
        if (\is_string($themeName) && '' !== $themeName) {
            return $themeName;
        }

        return null;
    }
}
