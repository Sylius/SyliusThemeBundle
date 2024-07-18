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

namespace Sylius\Bundle\ThemeBundle\Collector;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @property $data array{used_theme: ?ThemeInterface, used_themes: ThemeInterface[], themes: ThemeInterface[]}
 */
final class ThemeCollector extends DataCollector
{
    private ThemeRepositoryInterface $themeRepository;

    private ThemeContextInterface $themeContext;

    private ThemeHierarchyProviderInterface $themeHierarchyProvider;

    public function __construct(
        ThemeRepositoryInterface $themeRepository,
        ThemeContextInterface $themeContext,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
    ) {
        $this->themeRepository = $themeRepository;
        $this->themeContext = $themeContext;
        $this->themeHierarchyProvider = $themeHierarchyProvider;

        $this->data = [
            'used_theme' => null,
            'used_themes' => [],
            'themes' => [],
        ];
    }

    public function getUsedTheme(): ?ThemeInterface
    {
        return $this->data['used_theme'];
    }

    /**
     * @return ThemeInterface[]
     */
    public function getUsedThemes(): array
    {
        return $this->data['used_themes'];
    }

    /**
     * @return ThemeInterface[]
     */
    public function getThemes(): array
    {
        return $this->data['themes'];
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $usedTheme = $this->themeContext->getTheme();

        /** @var ThemeInterface[] $usedThemes */
        $usedThemes = null !== $usedTheme ? $this->themeHierarchyProvider->getThemeHierarchy($usedTheme) : [];

        /** @var ThemeInterface[] $themes */
        $themes = $this->themeRepository->findAll();

        $this->data['used_theme'] = $usedTheme;
        $this->data['used_themes'] = $usedThemes;
        $this->data['themes'] = $themes;
    }

    public function reset(): void
    {
        $this->data['used_theme'] = null;
        $this->data['used_themes'] = [];
        $this->data['themes'] = [];
    }

    public function getName(): string
    {
        return 'sylius_theme';
    }
}
