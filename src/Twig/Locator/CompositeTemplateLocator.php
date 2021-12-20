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

namespace Sylius\Bundle\ThemeBundle\Twig\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class CompositeTemplateLocator implements TemplateLocatorInterface
{
    /**
     * @psalm-var iterable<TemplateLocatorInterface>
     *
     * @var iterable|TemplateLocatorInterface[]
     */
    private iterable $themedTemplateLocators;

    /**
     * @psalm-param iterable<TemplateLocatorInterface> $themedTemplateLocators
     *
     * @param iterable|TemplateLocatorInterface[] $themedTemplateLocators
     */
    public function __construct(iterable $themedTemplateLocators)
    {
        $this->themedTemplateLocators = $themedTemplateLocators;
    }

    public function locate(string $template, ThemeInterface $theme): string
    {
        foreach ($this->themedTemplateLocators as $themedTemplateLocator) {
            if (!$themedTemplateLocator->supports($template)) {
                continue;
            }

            try {
                return $themedTemplateLocator->locate($template, $theme);
            } catch (TemplateNotFoundException $exception) {
                // Do nothing.
            }
        }

        throw new TemplateNotFoundException($template, [$theme]);
    }

    public function supports(string $template): bool
    {
        foreach ($this->themedTemplateLocators as $themedTemplateLocator) {
            if ($themedTemplateLocator->supports($template)) {
                return true;
            }
        }

        return false;
    }
}
