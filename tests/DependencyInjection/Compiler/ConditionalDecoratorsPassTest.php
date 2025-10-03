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

namespace Sylius\Bundle\ThemeBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\DependencyInjection\Compiler\ConditionalDecoratorsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ConditionalDecoratorsPassTest extends TestCase
{
    private ConditionalDecoratorsPass $compilerPass;

    protected function setUp(): void
    {
        $this->compilerPass = new ConditionalDecoratorsPass();
    }

    /** @test */
    public function it_does_not_remove_decorators_when_optimization_is_disabled(): void
    {
        $container = $this->createContainerWithDecorators();
        $container->setParameter('sylius_theme.optimize_empty', false);

        $this->compilerPass->process($container);

        $this->assertTrue($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'));
        $this->assertTrue($container->hasDefinition('Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator'));
    }

    /** @test */
    public function it_does_not_remove_decorators_when_parameter_is_not_set(): void
    {
        $container = $this->createContainerWithDecorators();

        $this->compilerPass->process($container);

        $this->assertTrue($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'));
    }

    /** @test */
    public function it_removes_decorators_when_optimization_is_enabled_and_no_themes_exist(): void
    {
        $container = $this->createContainerWithDecorators();
        $container->setParameter('sylius_theme.optimize_empty', true);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());

        // Configure empty filesystem source
        $container->setExtensionConfig('sylius_theme', [
            [
                'sources' => [
                    'filesystem' => [
                        'enabled' => true,
                        'directories' => [sys_get_temp_dir() . '/empty_themes'],
                        'filename' => 'composer.json',
                    ],
                ],
            ],
        ]);

        $this->compilerPass->process($container);

        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'));
        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator'));
        $this->assertTrue($container->getParameter('sylius_theme.decorators_removed'));
    }

    /** @test */
    public function it_keeps_decorators_when_filesystem_source_is_disabled(): void
    {
        $container = $this->createContainerWithDecorators();
        $container->setParameter('sylius_theme.optimize_empty', true);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());

        $container->setExtensionConfig('sylius_theme', [
            [
                'sources' => [
                    'filesystem' => [
                        'enabled' => false,
                    ],
                ],
            ],
        ]);

        $this->compilerPass->process($container);

        // When no sources enabled, decorators are removed (safe to optimize)
        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'));
    }

    /** @test */
    public function it_removes_template_locators_along_with_decorators(): void
    {
        $container = $this->createContainerWithDecorators();
        $container->setParameter('sylius_theme.optimize_empty', true);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());

        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Twig\Locator\ApplicationTemplateLocator',
            new Definition(),
        );
        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface',
            new Definition(),
        );

        $container->setExtensionConfig('sylius_theme', [
            [
                'sources' => [
                    'filesystem' => [
                        'enabled' => true,
                        'directories' => [sys_get_temp_dir() . '/empty'],
                    ],
                ],
            ],
        ]);

        $this->compilerPass->process($container);

        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Locator\ApplicationTemplateLocator'));
        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface'));
    }

    /** @test */
    public function it_removes_asset_services_along_with_decorators(): void
    {
        $container = $this->createContainerWithDecorators();
        $container->setParameter('sylius_theme.optimize_empty', true);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());

        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Asset\PathResolver',
            new Definition(),
        );

        $container->setExtensionConfig('sylius_theme', [
            [
                'sources' => [
                    'filesystem' => [
                        'enabled' => true,
                        'directories' => [sys_get_temp_dir() . '/empty'],
                    ],
                ],
            ],
        ]);

        $this->compilerPass->process($container);

        $this->assertFalse($container->hasDefinition('Sylius\Bundle\ThemeBundle\Asset\PathResolver'));
    }

    /** @test */
    public function it_handles_missing_services_gracefully(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('sylius_theme.optimize_empty', true);
        $container->setParameter('kernel.project_dir', sys_get_temp_dir());

        $container->setExtensionConfig('sylius_theme', [
            [
                'sources' => [
                    'filesystem' => [
                        'enabled' => true,
                        'directories' => [sys_get_temp_dir() . '/empty'],
                    ],
                ],
            ],
        ]);

        // Should not throw exception when services don't exist
        $this->compilerPass->process($container);

        $this->assertTrue($container->getParameter('sylius_theme.decorators_removed'));
    }

    private function createContainerWithDecorators(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader',
            new Definition(),
        );

        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator',
            new Definition(),
        );

        $container->setDefinition(
            'Sylius\Bundle\ThemeBundle\Translation\Translator',
            new Definition(),
        );

        return $container;
    }
}
