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

namespace Sylius\Bundle\ThemeBundle\Tests\Application;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Sylius\Bundle\ThemeBundle\Tests\Application\TestBundle\TestBundle;
use Sylius\Bundle\ThemeBundle\SyliusThemeBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as HttpKernel;

final class Kernel extends HttpKernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new TestBundle(),
            new SyliusThemeBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');

        if ($this->environment === 'test_legacy') {
            $loader->load(__DIR__ . '/config/config_test_legacy.yml');
        }
    }
}
