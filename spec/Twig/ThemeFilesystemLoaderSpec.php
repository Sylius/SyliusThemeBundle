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

namespace spec\Sylius\Bundle\ThemeBundle\Twig;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Templating\Locator\TemplateLocatorInterface;
use Twig\Loader\LoaderInterface;

final class ThemeFilesystemLoaderSpec extends ObjectBehavior
{
    function let(
        LoaderInterface $decoratedLoader,
        TemplateLocatorInterface $templateLocator
    ): void {
        $this->beConstructedWith($decoratedLoader, $templateLocator);
    }

    function it_gets_source_context_for_a_template_name(TemplateLocatorInterface $templateLocator): void
    {
        $templateLocator->locate('theme_test')->willReturn('file');

        $this->getCacheKey('theme_test')->shouldReturn('file');
    }
}
