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

namespace Sylius\Bundle\ThemeBundle\Tests\Translation\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sylius\Bundle\ThemeBundle\Translation\DependencyInjection\Compiler\TranslatorFallbackLocalesPass;
use Sylius\Bundle\ThemeBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TranslatorFallbackLocalesPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function it_copies_method_call_that_sets_fallback_locales_to_theme_translator(): void
    {
        $symfonyTranslatorDefinition = new Definition();
        $symfonyTranslatorDefinition->addMethodCall('setFallbackLocales', ['pl_PL']);
        $this->setDefinition('translator.default', $symfonyTranslatorDefinition);

        $this->setDefinition(Translator::class, new Definition());

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            Translator::class,
            'setFallbackLocales',
            ['pl_PL'],
        );
    }

    /**
     * @test
     */
    public function it_filters_out_other_method_calls_to_symfony_translator(): void
    {
        $symfonyTranslatorDefinition = new Definition();
        $symfonyTranslatorDefinition->addMethodCall('doFooAndBar', ['argument1', 'argument2']);
        $symfonyTranslatorDefinition->addMethodCall('setFallbackLocales', ['pl_PL']);
        $symfonyTranslatorDefinition->addMethodCall('doFoo', ['argument1']);
        $this->setDefinition('translator.default', $symfonyTranslatorDefinition);

        $this->setDefinition(Translator::class, new Definition());

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            Translator::class,
            'setFallbackLocales',
            ['pl_PL'],
        );
    }

    /**
     * @test
     */
    public function it_copies_method_calls_that_set_fallback_locales_to_theme_translator(): void
    {
        $symfonyTranslatorDefinition = new Definition();
        $symfonyTranslatorDefinition->addMethodCall('setFallbackLocales', ['pl_PL']);
        $symfonyTranslatorDefinition->addMethodCall('setFallbackLocales', ['en_US']);
        $this->setDefinition('translator.default', $symfonyTranslatorDefinition);

        $this->setDefinition(Translator::class, new Definition());

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            Translator::class,
            'setFallbackLocales',
            ['pl_PL'],
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            Translator::class,
            'setFallbackLocales',
            ['en_US'],
        );
    }

    /**
     * @test
     */
    public function it_does_not_force_symfony_translator_to_have_any_method_calls(): void
    {
        $this->setDefinition('translator.default', new Definition());
        $this->setDefinition(Translator::class, new Definition());

        $this->compile();

        $this->assertContainerBuilderHasService('translator.default');
        $this->assertContainerBuilderHasService(Translator::class);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TranslatorFallbackLocalesPass());
    }
}
