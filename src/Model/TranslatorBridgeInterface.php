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

namespace Sylius\Bundle\ThemeBundle\Model;

use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * This Interface is created for backward compatibility after removing Symfony\Component\Translation\TranslatorInterface in Symfony 5.0
 */
interface TranslatorBridgeInterface extends LocaleAwareInterface
{
    public function transChoice(
        string $id,
        int $number,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null
    ): string;
}
