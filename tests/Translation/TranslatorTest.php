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

namespace Sylius\Bundle\ThemeBundle\Tests\Translation;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProvider;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\SymfonyTranslatorResourceProvider;
use Sylius\Bundle\ThemeBundle\Translation\Translator;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @see \Symfony\Component\Translation\Tests\TranslatorTest
 */
final class TranslatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider getInvalidOptionsTests
     */
    public function it_throws_exception_on_instantiating_with_invalid_options(array $options): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createTranslator('en', $options);
    }

    /**
     * @test
     * @dataProvider getValidOptionsTests
     */
    public function it_instantiates_with_valid_options(array $options): void
    {
        Assert::assertInstanceOf(Translator::class, $this->createTranslator('en', $options));
    }

    /**
     * @test
     * @dataProvider getInvalidLocalesTests
     */
    public function it_throws_exception_on_instantiating_with_invalid_locale(string $locale): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createTranslator($locale);
    }

    /**
     * @test
     * @dataProvider getAllValidLocalesTests
     */
    public function it_instantiates_with_valid_locale(string $locale): void
    {
        $translator = $this->createTranslator($locale);

        $this->assertEquals($locale, $translator->getLocale());
    }

    /**
     * @test
     * @dataProvider getInvalidLocalesTests
     */
    public function its_throws_exception_on_setting_invalid_fallback_locales(string $locale): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $translator = $this->createTranslator('fr');
        $translator->setFallbackLocales(['fr', $locale]);
    }

    /**
     * @test
     * @dataProvider getAllValidLocalesTests
     */
    public function its_fallback_locales_can_be_set_only_if_valid(string $locale): void
    {
        $translator = $this->createTranslator('fr');
        $translator->setFallbackLocales(['fr', $locale]);

        Assert::assertSame(['fr', $locale], $translator->getFallbackLocales());
    }

    /**
     * @test
     * @dataProvider getAllValidLocalesTests
     */
    public function it_adds_resources_with_valid_locales(string $locale): void
    {
        $translator = $this->createTranslator('fr');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foofoo'], $locale);

        Assert::assertSame('foofoo', $translator->trans('foo', [], null, $locale));
    }

    /**
     * @test
     * @dataProvider getAllValidLocalesTests
     */
    public function it_translates_valid_locales(string $locale): void
    {
        $translator = $this->createTranslator($locale);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['test' => 'OK'], $locale);

        $this->assertEquals('OK', $translator->trans('test'));
        $this->assertEquals('OK', $translator->trans('test', [], null, $locale));
    }

    /**
     * @test
     */
    public function it_translates_to_a_fallback_locale(): void
    {
        $translator = $this->createTranslator('en');
        $translator->setFallbackLocales(['fr']);

        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foofoo'], 'en');
        $translator->addResource('array', ['bar' => 'foobar'], 'fr');

        $this->assertEquals('foobar', $translator->trans('bar'));
    }

    /**
     * @test
     */
    public function it_can_have_multiple_fallback_locales(): void
    {
        $translator = $this->createTranslator('en');
        $translator->setFallbackLocales(['de', 'fr']);

        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foo (en)'], 'en');
        $translator->addResource('array', ['bar' => 'bar (fr)'], 'fr');
        $translator->addResource('array', ['foobar' => 'foobar (de)'], 'de');

        $this->assertEquals('bar (fr)', $translator->trans('bar'));
        $this->assertEquals('foobar (de)', $translator->trans('foobar'));
    }

    /**
     * @test
     * @dataProvider getThemelessLocalesTests
     */
    public function it_gets_catalogue_with_fallback_catalogues_of_a_simple_locale(string $locale): void
    {
        $translator = $this->createTranslator($locale);
        $catalogue = new MessageCatalogue($locale);

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    /**
     * @test
     * @dataProvider getThemedLocalesTests
     */
    public function it_gets_catalogue_with_fallback_catalogues_of_a_themed_locale(string $locale): void
    {
        $translator = $this->createTranslator($locale);

        $catalogue = new MessageCatalogue($locale);
        $themeDelimiter = (int) strrpos($locale, '@');

        $catalogue->addFallbackCatalogue(new MessageCatalogue(substr($locale, 0, $themeDelimiter)));

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    /**
     * @test
     */
    public function it_creates_a_nested_catalogue_with_fallback_translations_of_a_territorial_locale(): void
    {
        $translator = $this->createTranslator('fr_FR');

        $catalogue = new MessageCatalogue('fr_FR');

        $fallback = new MessageCatalogue('fr');
        $catalogue->addFallbackCatalogue($fallback);

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    /**
     * @test
     */
    public function it_creates_a_nested_catalogue_with_fallback_translations_of_a_themed_locale(): void
    {
        $translator = $this->createTranslator('fr_FR@heron');

        $catalogue = new MessageCatalogue('fr_FR@heron');

        $firstFallback = new MessageCatalogue('fr_FR');
        $catalogue->addFallbackCatalogue($firstFallback);

        $secondFallback = new MessageCatalogue('fr@heron');
        $firstFallback->addFallbackCatalogue($secondFallback);

        $thirdFallback = new MessageCatalogue('fr');
        $secondFallback->addFallbackCatalogue($thirdFallback);

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    /**
     * @test
     */
    public function it_creates_a_nested_catalogue_with_fallback_translations_with_duplicated_additional_fallbacks(): void
    {
        $translator = $this->createTranslator('fr_FR@heron');
        $translator->setFallbackLocales(['fr_FR', 'fr']);

        $catalogue = new MessageCatalogue('fr_FR@heron');

        $firstFallback = new MessageCatalogue('fr_FR');
        $catalogue->addFallbackCatalogue($firstFallback);

        $secondFallback = new MessageCatalogue('fr@heron');
        $firstFallback->addFallbackCatalogue($secondFallback);

        $thirdFallback = new MessageCatalogue('fr');
        $secondFallback->addFallbackCatalogue($thirdFallback);

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    /**
     * @test
     */
    public function it_creates_a_nested_catalogue_with_fallback_translations(): void
    {
        $translator = $this->createTranslator('fr_FR@heron');
        $translator->setFallbackLocales(['en_US', 'en']);

        $catalogue = new MessageCatalogue('fr_FR@heron');

        $firstFallback = new MessageCatalogue('fr_FR');
        $catalogue->addFallbackCatalogue($firstFallback);

        $secondFallback = new MessageCatalogue('fr@heron');
        $firstFallback->addFallbackCatalogue($secondFallback);

        $thirdFallback = new MessageCatalogue('fr');
        $secondFallback->addFallbackCatalogue($thirdFallback);

        $fourthFallback = new MessageCatalogue('en_US@heron');
        $thirdFallback->addFallbackCatalogue($fourthFallback);

        $fifthFallback = new MessageCatalogue('en_US');
        $fourthFallback->addFallbackCatalogue($fifthFallback);

        $sixthFallback = new MessageCatalogue('en@heron');
        $fifthFallback->addFallbackCatalogue($sixthFallback);

        $seventhFallback = new MessageCatalogue('en');
        $sixthFallback->addFallbackCatalogue($seventhFallback);

        $this->assertEquals($catalogue, $translator->getCatalogue());
    }

    public function getInvalidLocalesTests(): array
    {
        return [
            ['fr FR'],
            ['français'],
            ['fr+en'],
            ['utf#8'],
            ['fr&en'],
            ['fr~FR'],
            [' fr'],
            ['fr '],
            ['fr*'],
            ['fr/FR'],
            ['fr\\FR'],
        ];
    }

    public function getAllValidLocalesTests(): array
    {
        return array_merge(
            $this->getThemedLocalesTests(),
            $this->getThemelessLocalesTests(),
        );
    }

    public function getThemedLocalesTests(): array
    {
        return [
            ['fr@heron'],
            ['francais@heron'],
            ['FR@heron'],
            ['frFR@heron'],
            ['fr.FR@heron'],
        ];
    }

    public function getThemelessLocalesTests(): array
    {
        return [
            ['fr'],
            ['francais'],
            ['FR'],
            ['frFR'],
            ['fr.FR'],
        ];
    }

    public function getValidOptionsTests(): array
    {
        return [
            [['cache_dir' => null, 'debug' => false]],
            [['cache_dir' => 'someDirectory', 'debug' => false]],
            [['debug' => false]],
            [['cache_dir' => 'yup']],
            [[]],
        ];
    }

    public function getInvalidOptionsTests(): array
    {
        return [
            [['heron' => '']],
            [['cache_dir' => null, 'pugs' => 'yes']],
            [['cache_dir' => null, 'debug' => false, 'pug' => 'heron']],
        ];
    }

    /**
     * @param string[] $options
     */
    private function createTranslator(string $locale = 'en', array $options = []): Translator
    {
        $loaderProvider = new TranslatorLoaderProvider();
        $resourceProvider = new SymfonyTranslatorResourceProvider();
        $messageFormatter = new MessageFormatter();

        return new Translator($loaderProvider, $resourceProvider, $messageFormatter, $locale, $options);
    }
}
