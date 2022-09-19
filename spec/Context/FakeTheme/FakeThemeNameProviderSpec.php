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
use Symfony\Component\HttpFoundation\Request;

final class FakeThemeNameProviderSpec extends ObjectBehavior
{
    public function it_implements_fake_theme_name_provider_interface(): void
    {
        $this->shouldImplement(FakeThemeNameProviderInterface::class);
    }

    public function it_returns_null_if_request_has_no_query_nor_cookie(): void
    {
        $this->getName(new Request())->shouldReturn(null);
    }

    public function it_returns_theme_name_from_query(): void
    {
        $request = new Request([FakeThemeNameProviderInterface::PARAMETER_NAME => 'test/theme']);
        $this->getName($request)->shouldReturn('test/theme');
    }

    public function it_returns_theme_name_from_cookie(): void
    {
        $request = new Request(cookies: [FakeThemeNameProviderInterface::PARAMETER_NAME => 'test/cookie-theme']);
        $this->getName($request)->shouldReturn('test/cookie-theme');
    }
}
