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

namespace Sylius\Bundle\ThemeBundle\Tests\Application\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class TemplatingController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderTemplateAction(string $templateName): Response
    {
        return new Response($this->twig->render($templateName));
    }
}
