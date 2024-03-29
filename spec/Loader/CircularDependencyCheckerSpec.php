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
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyCheckerInterface;
use Sylius\Bundle\ThemeBundle\Loader\CircularDependencyFoundException;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class CircularDependencyCheckerSpec extends ObjectBehavior
{
    function it_implements_circular_dependency_checker_interface(): void
    {
        $this->shouldImplement(CircularDependencyCheckerInterface::class);
    }

    function it_does_not_find_circular_dependency_if_checking_a_theme_without_any_parents(
        ThemeInterface $theme,
    ): void {
        $theme->getParents()->willReturn([]);

        $this->check($theme);
    }

    function it_does_not_find_circular_dependency_if_theme_parents_are_not_cycled(
        ThemeInterface $firstTheme,
        ThemeInterface $secondTheme,
        ThemeInterface $thirdTheme,
        ThemeInterface $fourthTheme,
    ): void {
        $firstTheme->getParents()->willReturn([$secondTheme, $thirdTheme]);
        $secondTheme->getParents()->willReturn([$thirdTheme, $fourthTheme]);
        $thirdTheme->getParents()->willReturn([$fourthTheme]);
        $fourthTheme->getParents()->willReturn([]);

        $this->check($firstTheme);
    }

    function it_finds_circular_dependency_if_theme_parents_are_cycled(
        ThemeInterface $firstTheme,
        ThemeInterface $secondTheme,
        ThemeInterface $thirdTheme,
        ThemeInterface $fourthTheme,
    ): void {
        $firstTheme->getParents()->willReturn([$secondTheme, $thirdTheme]);
        $secondTheme->getParents()->willReturn([$thirdTheme]);
        $thirdTheme->getParents()->willReturn([$fourthTheme]);
        $fourthTheme->getParents()->willReturn([$secondTheme]);

        $firstTheme->getName()->willReturn('first/theme');
        $secondTheme->getName()->willReturn('second/theme');
        $thirdTheme->getName()->willReturn('third/theme');
        $fourthTheme->getName()->willReturn('fourth/theme');

        $this
            ->shouldThrow(CircularDependencyFoundException::class)
            ->during('check', [$firstTheme])
        ;
    }
}
