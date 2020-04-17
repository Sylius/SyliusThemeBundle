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

namespace spec\Sylius\Bundle\ThemeBundle\Twig\Locator;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class TemplateNotFoundExceptionSpec extends ObjectBehavior
{
    function let(ThemeInterface $theme): void
    {
        $theme->getName()->willReturn('theme/name');

        $this->beConstructedWith('resource name', [$theme]);
    }

    function it_is_a_runtime_exception(): void
    {
        $this->shouldHaveType(\RuntimeException::class);
    }

    function it_has_custom_message(): void
    {
        $this->getMessage()->shouldReturn('Could not find template "resource name" using theme(s) "theme/name".');
    }

    function it_has_custom_message_with_multiple_themes(ThemeInterface $firstTheme, ThemeInterface $secondTheme): void
    {
        $firstTheme->getName()->willReturn('theme/first');
        $secondTheme->getName()->willReturn('theme/second');

        $this->beConstructedWith('resource name', [$firstTheme, $secondTheme]);

        $this->getMessage()->shouldReturn('Could not find template "resource name" using theme(s) "theme/first", "theme/second".');
    }
}
