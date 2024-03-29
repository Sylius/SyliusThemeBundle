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

namespace spec\Sylius\Bundle\ThemeBundle\Translation\Finder;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

final class TranslationFilesFinderSpec extends ObjectBehavior
{
    function let(FinderFactoryInterface $finderFactory): void
    {
        $this->beConstructedWith($finderFactory);
    }

    function it_implements_translation_resource_finder_interface(): void
    {
        $this->shouldImplement(TranslationFilesFinderInterface::class);
    }

    function it_returns_an_array_of_translation_resources_paths(
        FinderFactoryInterface $finderFactory,
        Finder $finder,
    ): void {
        $finderFactory->create()->willReturn($finder);

        $finder->ignoreUnreadableDirs()->shouldBeCalled()->willReturn($finder);
        $finder->in('/theme/translations')->shouldBeCalled()->willReturn($finder);

        $finder->getIterator()->willReturn(new \ArrayIterator([
            '/theme/translations/messages.en.yml',
            '/theme/translations/messages.en.yml.jpg',
            '/theme/translations/messages.yml',
        ]));

        $this->findTranslationFiles('/theme')->shouldReturn([
            '/theme/translations/messages.en.yml',
        ]);
    }

    function it_does_not_provide_any_translation_resources_paths_if_translation_directory_does_not_exist(
        FinderFactoryInterface $finderFactory,
        Finder $finder,
    ): void {
        $finderFactory->create()->willReturn($finder);

        $finder->ignoreUnreadableDirs()->willReturn($finder);
        $finder->in('/theme/translations')->willThrow(DirectoryNotFoundException::class);

        $this->findTranslationFiles('/theme')->shouldReturn([]);
    }
}
