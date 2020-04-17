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

namespace Sylius\Bundle\ThemeBundle\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class ResourceLocator implements ResourceLocatorInterface
{
    /** @var ResourceLocatorInterface */
    private $applicationResourceLocator;

    /** @var ResourceLocatorInterface */
    private $bundleResourceLocator;

    public function __construct(
        ResourceLocatorInterface $applicationResourceLocator,
        ResourceLocatorInterface $bundleResourceLocator
    ) {
        $this->applicationResourceLocator = $applicationResourceLocator;
        $this->bundleResourceLocator = $bundleResourceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function locateResource(string $template, ThemeInterface $theme): string
    {
        if (0 === strpos($template, '@')) {
            return $this->bundleResourceLocator->locateResource($template, $theme);
        }

        return $this->applicationResourceLocator->locateResource($template, $theme);
    }
}
