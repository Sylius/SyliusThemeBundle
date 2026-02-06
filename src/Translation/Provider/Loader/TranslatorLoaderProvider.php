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

namespace Sylius\Bundle\ThemeBundle\Translation\Provider\Loader;

use Symfony\Component\Translation\Loader\LoaderInterface;

final class TranslatorLoaderProvider implements TranslatorLoaderProviderInterface
{
    /** @var LoaderInterface[] */
    private array $loaders;

    /**
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        $this->loaders = $loaders;
    }

    public function getLoaders(): array
    {
        return $this->loaders;
    }
}
