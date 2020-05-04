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

namespace Sylius\Bundle\ThemeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TranslationTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_respects_theming_logic_while_translating_messages(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/template/Translation/translationsTest.txt.twig');

        foreach ($this->getTranslationsLines() as $expectedContent) {
            $this->assertStringContainsString($expectedContent, $crawler->text());
        }
    }

    private function getTranslationsLines(): array
    {
        return [
            'translations: translations',
            'BUNDLE/Resources/translations: BUNDLE/Resources/translations',
            'THEME/translations: THEME/translations',
            'PARENT_THEME/translations: PARENT_THEME/translations',
        ];
    }

    /**
     * @test
     * @group legacy
     */
    public function it_respects_legacy_theming_logic_while_translating_messages(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/template/Translation/legacyTranslationsTest.txt.twig');

        foreach ($this->getLegacyTranslationsLines() as $expectedContent) {
            $this->assertStringContainsString($expectedContent, $crawler->text());
        }
    }

    private function getLegacyTranslationsLines(): array
    {
        return [
            'THEME/translations: THEME/translations',
            'THEME/TestBundle/translations: THEME/TestBundle/translations',
            'THEME/TestPlugin/translations: THEME/TestPlugin/translations',
        ];
    }
}
