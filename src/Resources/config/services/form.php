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

use Sylius\Bundle\ThemeBundle\Form\Type\ThemeChoiceType;
use Sylius\Bundle\ThemeBundle\Form\Type\ThemeNameChoiceType;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ThemeChoiceType::class)
        ->args([service(ThemeRepositoryInterface::class)])
        ->tag('form.type')
    ;

    $services
        ->set(ThemeNameChoiceType::class)
        ->args([service(ThemeRepositoryInterface::class)])
        ->tag('form.type')
    ;
};
