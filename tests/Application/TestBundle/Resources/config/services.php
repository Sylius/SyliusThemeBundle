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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\ThemeBundle\Tests\Application\TestBundle\Controller\TemplatingController;
use Sylius\Bundle\ThemeBundle\Tests\Application\TestBundle\Listener\RequestListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()->public();

    $services
        ->set(TemplatingController::class)
        ->args([
            service('twig'),
        ]);

    $services
        ->set('test.sylius_theme.request_listener', RequestListener::class)
        ->args([
            service('sylius.repository.theme'),
            service('sylius.theme.context.settable'),
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest']);
};
