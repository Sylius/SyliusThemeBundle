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
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

final class ThemeFilesystemLoaderSpec extends ObjectBehavior
{
    function let(
        \Twig_LoaderInterface $decoratedLoader,
        FileLocatorInterface $templateLocator,
        TemplateNameParserInterface $templateNameParser
    ): void {
        $this->beConstructedWith($decoratedLoader, $templateLocator, $templateNameParser);
    }

    function it_gets_source_context_for_a_template_name(
        TemplateNameParserInterface $templateNameParser,
        FileLocatorInterface $templateLocator
    ): void {
        $templateNameParser->parse('theme_test')->willReturn('@Theme/test');
        $templateLocator->locate('@Theme/test')->willReturn('file');

        $this->getCacheKey('theme_test')->shouldReturn('file');
    }

    function it_gets_cache_key_for_a_template_reference(
        TemplateNameParserInterface $templateNameParser,
        FileLocatorInterface $templateLocator,
        TemplateReferenceInterface $templateReference
    ): void {
        $templateReference->__toString()->willReturn('theme_test');

        $templateNameParser->parse('theme_test')->willReturn('@Theme/test');
        $templateLocator->locate('@Theme/test')->willReturn('file');

        $this->getCacheKey('theme_test')->shouldReturn('file');
    }
}
