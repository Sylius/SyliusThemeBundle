<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ThemeBundle\Tests\Twig\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProvider;
use Sylius\Bundle\ThemeBundle\Model\Theme;
use Sylius\Bundle\ThemeBundle\Twig\Extension\WebpackEncoreExtension;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException;

final class WebpackEncoreExtensionTest extends TestCase
{
    private const BUILD_NAME = 'themes';

    /** @var ThemeContextInterface&MockObject */
    private ThemeContextInterface $themeContext;

    /** @var EntrypointLookupCollectionInterface&MockObject */
    private EntrypointLookupCollectionInterface $entrypointLookupCollection;

    /** @var TagRenderer&MockObject */
    private TagRenderer $tagRenderer;

    private WebpackEncoreExtension $extension;

    private string $entrypointsPath;

    protected function setUp(): void
    {
        $this->themeContext = $this->createMock(ThemeContextInterface::class);
        $this->entrypointLookupCollection = $this->createMock(EntrypointLookupCollectionInterface::class);
        $this->tagRenderer = $this->createMock(TagRenderer::class);

        $this->entrypointsPath = sys_get_temp_dir() . '/' . uniqid('sylius_theme_entrypoints_', true) . '.json';

        $this->extension = new WebpackEncoreExtension(
            $this->themeContext,
            new ThemeHierarchyProvider(),
            $this->entrypointLookupCollection,
            $this->tagRenderer,
        );
    }

    protected function tearDown(): void
    {
        if (file_exists($this->entrypointsPath)) {
            unlink($this->entrypointsPath);
        }
    }

    /**
     * @test
     */
    public function it_renders_nothing_when_there_is_no_active_theme(): void
    {
        $this->themeContext->method('getTheme')->willReturn(null);

        $this->tagRenderer->expects($this->never())->method('renderWebpackLinkTags');
        $this->tagRenderer->expects($this->never())->method('renderWebpackScriptTags');

        $this->assertSame('', $this->extension->renderLinkTags(self::BUILD_NAME));
        $this->assertSame('', $this->extension->renderScriptTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_tags_of_the_active_theme_entry(): void
    {
        $this->mockActiveTheme('acme/theme');
        $this->mockEntrypoints(['acme/theme' => ['css' => ['/build/theme.css'], 'js' => ['/build/theme.js']]]);

        $this->tagRenderer
            ->expects($this->once())
            ->method('renderWebpackLinkTags')
            ->with('acme/theme', 'package', self::BUILD_NAME, ['media' => 'all'])
            ->willReturn('<link rel="stylesheet" href="/build/theme.css">')
        ;
        $this->tagRenderer
            ->expects($this->once())
            ->method('renderWebpackScriptTags')
            ->with('acme/theme', null, self::BUILD_NAME, [])
            ->willReturn('<script src="/build/theme.js"></script>')
        ;

        $this->assertSame('<link rel="stylesheet" href="/build/theme.css">', $this->extension->renderLinkTags(self::BUILD_NAME, 'package', ['media' => 'all']));
        $this->assertSame('<script src="/build/theme.js"></script>', $this->extension->renderScriptTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_tags_of_the_parent_theme_entry_when_the_active_theme_has_no_entry(): void
    {
        $this->mockActiveTheme('acme/child-theme', $this->createTheme('acme/parent-theme'));
        $this->mockEntrypoints(['acme/parent-theme' => ['css' => ['/build/parent.css']]]);

        $this->tagRenderer
            ->expects($this->once())
            ->method('renderWebpackLinkTags')
            ->with('acme/parent-theme', null, self::BUILD_NAME, [])
            ->willReturn('<link rel="stylesheet" href="/build/parent.css">')
        ;

        $this->assertSame('<link rel="stylesheet" href="/build/parent.css">', $this->extension->renderLinkTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_prefers_the_active_theme_entry_over_the_parent_theme_entry(): void
    {
        $this->mockActiveTheme('acme/child-theme', $this->createTheme('acme/parent-theme'));
        $this->mockEntrypoints([
            'acme/child-theme' => ['css' => ['/build/child.css']],
            'acme/parent-theme' => ['css' => ['/build/parent.css'], 'js' => ['/build/parent.js']],
        ]);

        $this->tagRenderer
            ->expects($this->once())
            ->method('renderWebpackScriptTags')
            ->with('acme/child-theme', null, self::BUILD_NAME, [])
            ->willReturn('')
        ;

        $this->assertSame('', $this->extension->renderScriptTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_tags_of_the_closest_ancestor_theme_having_an_entry(): void
    {
        $this->mockActiveTheme(
            'acme/child-theme',
            $this->createTheme('acme/first-parent-theme'),
            $this->createTheme('acme/second-parent-theme', $this->createTheme('acme/grandparent-theme')),
        );
        $this->mockEntrypoints([
            'acme/grandparent-theme' => ['css' => ['/build/grandparent.css']],
            'acme/unrelated-theme' => ['css' => ['/build/unrelated.css']],
        ]);

        $this->tagRenderer
            ->expects($this->once())
            ->method('renderWebpackLinkTags')
            ->with('acme/grandparent-theme', null, self::BUILD_NAME, [])
            ->willReturn('<link rel="stylesheet" href="/build/grandparent.css">')
        ;

        $this->assertSame('<link rel="stylesheet" href="/build/grandparent.css">', $this->extension->renderLinkTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_nothing_when_no_theme_in_the_hierarchy_has_an_entry(): void
    {
        $this->mockActiveTheme('acme/child-theme', $this->createTheme('acme/parent-theme'));
        $this->mockEntrypoints(['acme/unrelated-theme' => ['css' => ['/build/unrelated.css']]]);

        $this->tagRenderer->expects($this->never())->method('renderWebpackLinkTags');
        $this->tagRenderer->expects($this->never())->method('renderWebpackScriptTags');

        $this->assertSame('', $this->extension->renderLinkTags(self::BUILD_NAME));
        $this->assertSame('', $this->extension->renderScriptTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_nothing_when_the_build_has_not_been_compiled(): void
    {
        $this->mockActiveTheme('acme/theme');
        $this->entrypointLookupCollection
            ->method('getEntrypointLookup')
            ->with(self::BUILD_NAME)
            ->willReturn(new EntrypointLookup($this->entrypointsPath))
        ;

        $this->tagRenderer->expects($this->never())->method('renderWebpackLinkTags');

        $this->assertSame('', $this->extension->renderLinkTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_renders_nothing_when_a_custom_entrypoint_lookup_does_not_know_the_active_theme_entry(): void
    {
        $this->mockActiveTheme('acme/theme');
        $this->entrypointLookupCollection
            ->method('getEntrypointLookup')
            ->with(self::BUILD_NAME)
            ->willReturn($this->createMock(EntrypointLookupInterface::class))
        ;

        $this->tagRenderer
            ->method('renderWebpackLinkTags')
            ->willThrowException(new EntrypointNotFoundException('Could not find the entry "acme/theme".'))
        ;
        $this->tagRenderer
            ->method('renderWebpackScriptTags')
            ->willThrowException(new \InvalidArgumentException('Could not find the entrypoints file from Webpack.'))
        ;

        $this->assertSame('', $this->extension->renderLinkTags(self::BUILD_NAME));
        $this->assertSame('', $this->extension->renderScriptTags(self::BUILD_NAME));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_the_build_is_not_defined(): void
    {
        $this->mockActiveTheme('acme/theme');
        $this->entrypointLookupCollection
            ->method('getEntrypointLookup')
            ->willThrowException(new UndefinedBuildException('The build "undefined" is not configured.'))
        ;

        $this->expectException(UndefinedBuildException::class);

        $this->extension->renderLinkTags('undefined');
    }

    private function mockActiveTheme(string $name, Theme ...$parents): void
    {
        $this->themeContext->method('getTheme')->willReturn($this->createTheme($name, ...$parents));
    }

    private function createTheme(string $name, Theme ...$parents): Theme
    {
        $theme = new Theme($name, '/themes/' . $name);
        foreach ($parents as $parent) {
            $theme->addParent($parent);
        }

        return $theme;
    }

    /**
     * @param array<string, array<string, string[]>> $entrypoints
     */
    private function mockEntrypoints(array $entrypoints): void
    {
        file_put_contents($this->entrypointsPath, json_encode(['entrypoints' => $entrypoints]));

        $this->entrypointLookupCollection
            ->method('getEntrypointLookup')
            ->with(self::BUILD_NAME)
            ->willReturn(new EntrypointLookup($this->entrypointsPath))
        ;
    }
}
