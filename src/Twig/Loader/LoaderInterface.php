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

namespace Sylius\Bundle\ThemeBundle\Twig\Loader;

use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;

if (class_exists(ExistsLoaderInterface::class)) {
    /**
     * Twig 2.x compatibility
     *
     * @internal
     */
    interface LoaderInterface extends TwigLoaderInterface, ExistsLoaderInterface
    {
    }
} else {
    /**
     * Twig 3.x compatibility
     *
     * @internal
     */
    interface LoaderInterface extends TwigLoaderInterface
    {
    }
}
