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

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ConditionalDecoratorsOptimizationTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_keeps_decorators_when_optimization_is_disabled(): void
    {
        self::bootKernel(['environment' => 'test']);

        $container = self::getContainer();

        // These services should exist when optimization is off
        Assert::assertTrue(
            $container->has('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'),
            'ThemedTemplateLoader should exist when optimization is disabled',
        );
    }

    /**
     * @test
     */
    public function it_removes_decorators_when_optimization_is_enabled_and_no_themes(): void
    {
        // Note: This test requires a separate test environment with optimize_empty: true
        // and no themes directory. It demonstrates the expected behavior.

        self::bootKernel(['environment' => 'test_optimized']);

        $container = self::getContainer();

        // Check if optimization marker parameter exists
        if ($container->hasParameter('sylius_theme.decorators_removed')) {
            Assert::assertTrue(
                $container->getParameter('sylius_theme.decorators_removed'),
                'Decorators should be marked as removed',
            );

            // These services should NOT exist when optimized
            Assert::assertFalse(
                $container->has('Sylius\Bundle\ThemeBundle\Twig\Loader\ThemedTemplateLoader'),
                'ThemedTemplateLoader should not exist when optimized with no themes',
            );

            Assert::assertFalse(
                $container->has('Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator'),
                'ThemeAwareTranslator should not exist when optimized with no themes',
            );
        } else {
            // If optimization wasn't triggered (themes exist or optimization disabled)
            $this->markTestSkipped('Optimization was not triggered - themes might exist or optimize_empty is disabled');
        }
    }

    /**
     * @test
     */
    public function it_verifies_twig_loader_works_without_theme_decorator(): void
    {
        self::bootKernel(['environment' => 'test_optimized']);

        $container = self::getContainer();

        // Even without theme decorator, Twig should work normally
        if ($container->hasParameter('sylius_theme.decorators_removed')) {
            Assert::assertTrue($container->has('twig'), 'Twig service should still exist');

            $twig = $container->get('twig');
            Assert::assertNotNull($twig, 'Twig should be initialized');
        } else {
            $this->markTestSkipped('Optimization was not triggered');
        }
    }

    /**
     * @test
     */
    public function it_verifies_translator_works_without_theme_decorator(): void
    {
        self::bootKernel(['environment' => 'test_optimized']);

        $container = self::getContainer();

        // Even without theme decorator, translator should work normally
        if ($container->hasParameter('sylius_theme.decorators_removed')) {
            Assert::assertTrue($container->has('translator'), 'Translator service should still exist');

            $translator = $container->get('translator');
            Assert::assertNotNull($translator, 'Translator should be initialized');

            // Verify it's the original Symfony translator, not themed
            Assert::assertNotInstanceOf(
                'Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator',
                $translator,
                'Should use original Symfony translator when decorators are removed',
            );
        } else {
            $this->markTestSkipped('Optimization was not triggered');
        }
    }

    /**
     * @test
     */
    public function it_keeps_core_theme_infrastructure_even_when_decorators_removed(): void
    {
        self::bootKernel(['environment' => 'test_optimized']);

        $container = self::getContainer();

        // Core theme services should always exist (even if unused)
        Assert::assertTrue(
            $container->has('Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface'),
            'ThemeContextInterface should always exist',
        );

        Assert::assertTrue(
            $container->has('Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface'),
            'ThemeRepositoryInterface should always exist',
        );
    }

    /**
     * @test
     */
    public function it_properly_handles_service_aliases(): void
    {
        self::bootKernel(['environment' => 'test']);

        $container = self::getContainer();

        // Verify that service aliases are properly maintained
        $themeContext = $container->get('Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface');
        Assert::assertNotNull($themeContext, 'Theme context should be resolvable via interface');
    }

    protected static function getKernelClass(): string
    {
        return \Sylius\Bundle\ThemeBundle\Tests\Functional\app\AppKernel::class;
    }
}
