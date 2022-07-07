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
use Sylius\Bundle\ThemeBundle\Translation\DependencyInjection\Compiler\TranslatorLoaderProviderPass;
use Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class TranslatorLoaderProviderPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function it_adds_translation_loaders_to_sylius_loader_provider(): void
    {
        $this->setDefinition(TranslatorLoaderProviderInterface::class, new Definition(null, [[]]));

        $translationLoaderDefinition = new Definition();
        $translationLoaderDefinition->addTag('translation.loader', ['alias' => 'yml']);
        $this->setDefinition('translation.loader.yml', $translationLoaderDefinition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TranslatorLoaderProviderInterface::class,
            0,
            ['yml' => new Reference('translation.loader.yml')],
        );
    }

    /**
     * @test
     */
    public function it_adds_translation_loaders_with_its_legacy_alias_to_sylius_loader_provider(): void
    {
        $this->setDefinition(TranslatorLoaderProviderInterface::class, new Definition(null, [[]]));

        $translationLoaderDefinition = new Definition();
        $translationLoaderDefinition->addTag('translation.loader', ['alias' => 'xlf', 'legacy-alias' => 'xliff']);
        $this->setDefinition('translation.loader.xliff', $translationLoaderDefinition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TranslatorLoaderProviderInterface::class,
            0,
            ['xlf' => new Reference('translation.loader.xliff'), 'xliff' => new Reference('translation.loader.xliff')],
        );
    }

    /**
     * @test
     */
    public function it_adds_translation_loaders_using_only_the_first_tag_alias(): void
    {
        $this->setDefinition(TranslatorLoaderProviderInterface::class, new Definition(null, [[]]));

        $translationLoaderDefinition = new Definition();
        $translationLoaderDefinition->addTag('translation.loader', ['alias' => 'yml']);
        $translationLoaderDefinition->addTag('translation.loader', ['alias' => 'yaml']);
        $this->setDefinition('translation.loader.yml', $translationLoaderDefinition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TranslatorLoaderProviderInterface::class,
            0,
            ['yml' => new Reference('translation.loader.yml')],
        );
    }

    /**
     * @test
     */
    public function it_does_not_force_the_existence_of_translation_loaders(): void
    {
        $this->setDefinition(TranslatorLoaderProviderInterface::class, new Definition(null, [[]]));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            TranslatorLoaderProviderInterface::class,
            0,
            [],
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TranslatorLoaderProviderPass());
    }
}
