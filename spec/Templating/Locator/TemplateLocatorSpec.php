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

namespace spec\Sylius\Bundle\ThemeBundle\Templating\Locator;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Locator\ResourceLocatorInterface;
use Sylius\Bundle\ThemeBundle\Templating\Locator\TemplateLocatorInterface;

final class TemplateLocatorSpec extends ObjectBehavior
{
    function let(
        ThemeContextInterface $themeContext,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        ResourceLocatorInterface $resourceLocator
    ): void {
        $this->beConstructedWith($themeContext, $themeHierarchyProvider, $resourceLocator);
    }

    function it_implements_template_locator_interface(): void
    {
        $this->shouldImplement(TemplateLocatorInterface::class);
    }
}
