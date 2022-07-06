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
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface;

final class OrderingTranslationFilesFinderSpec extends ObjectBehavior
{
    function let(TranslationFilesFinderInterface $translationFilesFinder): void
    {
        $this->beConstructedWith($translationFilesFinder);
    }

    function it_is_a_translation_files_finder(): void
    {
        $this->shouldImplement(TranslationFilesFinderInterface::class);
    }

    function it_puts_application_translations_files_before_bundle_translations_files(
        TranslationFilesFinderInterface $translationFilesFinder,
    ): void {
        $translationFilesFinder->findTranslationFiles('/some/path/to/theme')->willReturn([
            '/some/path/to/theme/AcmeBundle/translations/messages.en.yml',
            '/some/path/to/theme/translations/messages.en.yml',
            '/some/path/to/theme/YcmeBundle/translations/messages.en.yml',
        ]);

        $this->findTranslationFiles('/some/path/to/theme')->shouldStartIteratingAs(['/some/path/to/theme/translations/messages.en.yml']);
    }
}
