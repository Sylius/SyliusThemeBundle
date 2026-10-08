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

namespace Sylius\Bundle\ThemeBundle\Twig\Extension;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class WebpackEncoreExtension extends AbstractExtension
{
    private ThemeContextInterface $themeContext;

    private ThemeHierarchyProviderInterface $themeHierarchyProvider;

    private EntrypointLookupCollectionInterface $entrypointLookupCollection;

    private TagRenderer $tagRenderer;

    public function __construct(
        ThemeContextInterface $themeContext,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        EntrypointLookupCollectionInterface $entrypointLookupCollection,
        TagRenderer $tagRenderer,
    ) {
        $this->themeContext = $themeContext;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
        $this->entrypointLookupCollection = $entrypointLookupCollection;
        $this->tagRenderer = $tagRenderer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_theme_encore_entry_link_tags', [$this, 'renderLinkTags'], ['is_safe' => ['html']]),
            new TwigFunction('sylius_theme_encore_entry_script_tags', [$this, 'renderScriptTags'], ['is_safe' => ['html']]),
        ];
    }

    public function renderLinkTags(string $entrypointName = '_default', ?string $packageName = null, array $attributes = []): string
    {
        $entryName = $this->resolveEntryName($entrypointName);
        if (null === $entryName) {
            return '';
        }

        try {
            return $this->tagRenderer->renderWebpackLinkTags($entryName, $packageName, $entrypointName, $attributes);
        } catch (\InvalidArgumentException) {
            return '';
        }
    }

    public function renderScriptTags(string $entrypointName = '_default', ?string $packageName = null, array $attributes = []): string
    {
        $entryName = $this->resolveEntryName($entrypointName);
        if (null === $entryName) {
            return '';
        }

        try {
            return $this->tagRenderer->renderWebpackScriptTags($entryName, $packageName, $entrypointName, $attributes);
        } catch (\InvalidArgumentException) {
            return '';
        }
    }

    private function resolveEntryName(string $entrypointName): ?string
    {
        $theme = $this->themeContext->getTheme();
        if (null === $theme) {
            return null;
        }

        $entrypointLookup = $this->entrypointLookupCollection->getEntrypointLookup($entrypointName);
        if (!$entrypointLookup instanceof EntrypointLookup) {
            return $theme->getName();
        }

        try {
            foreach ($this->themeHierarchyProvider->getThemeHierarchy($theme) as $hierarchyTheme) {
                if ($entrypointLookup->entryExists($hierarchyTheme->getName())) {
                    return $hierarchyTheme->getName();
                }
            }
        } catch (\InvalidArgumentException) {
            return null;
        }

        return null;
    }
}
