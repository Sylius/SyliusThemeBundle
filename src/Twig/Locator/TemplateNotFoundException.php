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

final class TemplateNotFoundException extends \RuntimeException
{
    /**
     * @param ThemeInterface[] $themes
     */
    public function __construct(string $template, array $themes)
    {
        parent::__construct(sprintf(
            'Could not find template "%s" using theme(s) "%s".',
            $template,
            implode(
                '", "',
                array_map(
                    static function (ThemeInterface $theme): string {
                        return $theme->getName();
                    },
                    $themes,
                ),
            ),
        ));
    }
}
