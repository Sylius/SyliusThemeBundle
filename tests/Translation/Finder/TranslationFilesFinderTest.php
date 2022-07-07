<?php

declare(strict_types=1);

namespace Sylius\Bundle\ThemeBundle\Tests\Translation\Finder;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinder;
use Symfony\Component\Finder\Finder;

final class TranslationFilesFinderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_follow_symlinks(): void
    {
        $finder = new Finder();
        $finderFactory = $this->createMock(FinderFactoryInterface::class);
        $finderFactory->method('create')->willReturn($finder);

        $translationsFilesFinder = new TranslationFilesFinder($finderFactory);
        $files = $translationsFilesFinder->findTranslationFiles('tests/Application/themes/MyTheme');

        $this->assertContains('tests/Application/themes/MyTheme/translations/messages.fr.yml', $files);
    }
}
