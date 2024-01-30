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

namespace Sylius\Bundle\ThemeBundle\Translation\DependencyInjection\Compiler;

use Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\SymfonyTranslatorResourceProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

final class TranslatorResourceProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $symfonyTranslator = $container->findDefinition('translator.default');
            $syliusResourceProvider = $container->findDefinition(SymfonyTranslatorResourceProvider::class);
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        $symfonyResourcesFiles = $this->extractResourcesFilesFromSymfonyTranslator($symfonyTranslator);

        $syliusResourceProvider->replaceArgument(0, array_merge(
            $syliusResourceProvider->getArgument(0),
            $symfonyResourcesFiles,
        ));
    }

    private function extractResourcesFilesFromSymfonyTranslator(Definition $symfonyTranslator): array
    {
        try {
            $options = $symfonyTranslator->getArgument(3);

            if (!is_array($options) || !isset($options['resource_files'])) {
                $options = $symfonyTranslator->getArgument(4);
            }
        } catch (OutOfBoundsException $exception) {
            $options = [];
        }

        $languagesFiles = isset($options['resource_files']) && is_iterable($options['resource_files']) ? $options['resource_files'] : [];

        $resourceFiles = [];
        foreach ($languagesFiles as $language => $files) {
            foreach ($files as $file) {
                $resourceFiles[] = $file;
            }
        }

        return $resourceFiles;
    }
}
