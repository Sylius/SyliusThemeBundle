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

namespace Sylius\Bundle\ThemeBundle\Repository;

use Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class InMemoryThemeRepository implements ThemeRepositoryInterface
{
    /** @var ThemeInterface[] */
    private array $themes = [];

    private ThemeLoaderInterface $themeLoader;

    private bool $themesLoaded = false;

    public function __construct(ThemeLoaderInterface $themeLoader)
    {
        $this->themeLoader = $themeLoader;
    }

    public function findAll(): array
    {
        $this->loadThemesIfNeeded();

        return $this->themes;
    }

    public function findOneByName(string $name): ?ThemeInterface
    {
        $this->loadThemesIfNeeded();

        return $this->themes[$name] ?? null;
    }

    public function findOneByTitle(string $title): ?ThemeInterface
    {
        $this->loadThemesIfNeeded();

        foreach ($this->themes as $theme) {
            if ($theme->getTitle() === $title) {
                return $theme;
            }
        }

        return null;
    }

    private function loadThemesIfNeeded(): void
    {
        if ($this->themesLoaded) {
            return;
        }

        $themes = $this->themeLoader->load();
        foreach ($themes as $theme) {
            $this->themes[$theme->getName()] = $theme;
        }

        $this->themesLoaded = true;
    }
}
