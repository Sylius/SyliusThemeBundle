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
            /** @psalm-suppress DocblockTypeContradiction Better safe than sorry */
            if (!$translator instanceof $interface) {
                /** @psalm-suppress NoValue Better safe than sorry */
                throw new \InvalidArgumentException(sprintf(
                    'The translator "%s" must implement %s.',
                    get_class($translator),
                    $interface,
                ));
            }
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
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

    /**
     * @psalm-suppress MissingParamType Two interfaces defining the same method
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $this->transformLocale($locale));
    }

    public function transChoice(string $id, int $number, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        if (!method_exists($this->translator, 'transChoice')) {
            throw new \RuntimeException(sprintf('%s::transChoice is not supported with symfony/translation v5', static::class));
        }

        return $this->translator->transChoice($id, $number, $parameters, $domain, $this->transformLocale($locale));
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

    /**
     * @psalm-suppress MissingParamType
     * @psalm-suppress MissingReturnType
     */
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
