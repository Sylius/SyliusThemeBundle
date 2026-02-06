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

namespace Sylius\Bundle\ThemeBundle\Translation;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ThemeAwareTranslator implements TranslatorInterface, TranslatorBagInterface, WarmableInterface, LocaleAwareInterface
{
    /** @var TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface */
    private TranslatorInterface $translator;

    private ThemeContextInterface $themeContext;

    /**
     * @param TranslatorInterface&LocaleAwareInterface&TranslatorBagInterface $translator
     */
    public function __construct(TranslatorInterface $translator, ThemeContextInterface $themeContext)
    {
        foreach ([LocaleAwareInterface::class, TranslatorBagInterface::class] as $interface) {
            if (!$translator instanceof $interface) {
                throw new \InvalidArgumentException(sprintf(
                    'The translator "%s" must implement %s.',
                    $translator::class,
                    $interface,
                ));
            }
        }

        $this->translator = $translator;
        $this->themeContext = $themeContext;
    }

    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call(string $method, array $arguments)
    {
        $translator = $this->translator;
        $arguments = array_values($arguments);

        return $translator->$method(...$arguments);
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $this->transformLocale($locale));
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale): void
    {
        /** @var string $locale */
        $locale = $this->transformLocale($locale);

        $this->translator->setLocale($locale);
    }

    /**
     * @param string|null $locale
     */
    public function getCatalogue($locale = null): MessageCatalogueInterface
    {
        return $this->translator->getCatalogue($locale);
    }

    public function warmUp($cacheDir, ?string $buildDir = null): array
    {
        if ($this->translator instanceof WarmableInterface) {
            return $this->translator->warmUp($cacheDir);
        }

        return [];
    }

    private function transformLocale(?string $locale): ?string
    {
        $theme = $this->themeContext->getTheme();

        if (null === $theme) {
            return $locale;
        }

        if (null === $locale) {
            $locale = $this->getLocale();
        }

        return $locale . '@' . str_replace('/', '-', $theme->getName());
    }

    public function getCatalogues(): array
    {
        return $this->translator->getCatalogues();
    }
}
