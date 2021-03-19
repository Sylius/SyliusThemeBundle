<?php

use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import('vendor/sylius-labs/coding-standard/ecs.php');

    $services = $containerConfigurator->services();
    $services->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => <<<HEA
This file is part of the Sylius package.

(c) Paweł Jędrzejewski

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEA,
            'location' => 'after_open',
        ]]);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SKIP, [
        '**/var/*',
        VisibilityRequiredFixer::class => ['*Spec.php'],
    ]);
};
