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

namespace Sylius\Bundle\ThemeBundle\Asset\Installer;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface AssetsProviderInterface
{
    /**
     * @psalm-return iterable<string, string> Maps origin dir to relative target dir
     */
    public function provideDirectoriesForTheme(ThemeInterface $rootTheme): iterable;

    /**
     * @psalm-return iterable<string, string> Maps origin dir to relative target dir
     */
    public function provideDirectoriesForBundle(BundleInterface $bundle): iterable;
}
