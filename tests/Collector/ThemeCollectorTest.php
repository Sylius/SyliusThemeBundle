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

namespace Sylius\Bundle\ThemeBundle\Tests\Collector;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\Collector\ThemeCollector;
use Sylius\Bundle\ThemeBundle\Context\EmptyThemeContext;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\NoopThemeHierarchyProvider;
use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;
use Sylius\Bundle\ThemeBundle\Repository\InMemoryThemeRepository;

class ThemeCollectorTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_no_used_theme(): void
    {
        $themeCollector = $this->createThemeCollector();

        $this->assertNull($themeCollector->getUsedTheme());
    }

    private function createThemeCollector(): ThemeCollector
    {
        $themeLoader = new class () implements ThemeLoaderInterface {
            public function load(): array
            {
                return [];
            }
        };

        $themeRepository = new InMemoryThemeRepository($themeLoader);
        $themeContext = new EmptyThemeContext();
        $themeHierarchyProvider = new NoopThemeHierarchyProvider();

        return new ThemeCollector(
            $themeRepository,
            $themeContext,
            $themeHierarchyProvider,
        );
    }
}
