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

namespace Sylius\Bundle\ThemeBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ThemeConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_theme');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->ignoreExtraKeys();

        $this->addRequiredNameField($rootNode);
        $this->addOptionalTitleField($rootNode);
        $this->addOptionalDescriptionField($rootNode);
        $this->addOptionalPathField($rootNode);
        $this->addOptionalParentsList($rootNode);
        $this->addOptionalScreenshotsList($rootNode);
        $this->addOptionalAuthorsList($rootNode);

        return $treeBuilder;
    }

    private function addRequiredNameField(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $rootNodeDefinition->children()->scalarNode('name')->isRequired()->cannotBeEmpty();
    }

    private function addOptionalTitleField(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $rootNodeDefinition->children()->scalarNode('title')->cannotBeEmpty();
    }

    private function addOptionalDescriptionField(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $rootNodeDefinition->children()->scalarNode('description')->cannotBeEmpty();
    }

    private function addOptionalPathField(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $rootNodeDefinition->children()->scalarNode('path')->cannotBeEmpty();
    }

    private function addOptionalParentsList(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $parentsNodeDefinition = $rootNodeDefinition->children()->arrayNode('parents');
        $parentsNodeDefinition
            ->requiresAtLeastOneElement()
            ->performNoDeepMerging()
                ->scalarPrototype()
                ->cannotBeEmpty()
        ;
    }

    private function addOptionalScreenshotsList(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $screenshotsNodeDefinition = $rootNodeDefinition->children()->arrayNode('screenshots');
        $screenshotsNodeDefinition
            ->requiresAtLeastOneElement()
            ->performNoDeepMerging()
        ;

        /** @var ArrayNodeDefinition $screenshotNodeDefinition */
        $screenshotNodeDefinition = $screenshotsNodeDefinition->arrayPrototype();

        $screenshotNodeDefinition
            ->validate()
                ->ifTrue(
                    /** @param mixed $screenshot */
                    function ($screenshot): bool {
                        return [] === $screenshot || ['path' => ''] === $screenshot;
                    },
                )
                ->thenInvalid('Screenshot cannot be empty!')
        ;
        $screenshotNodeDefinition
            ->beforeNormalization()
                ->ifString()
                ->then(
                    /** @param mixed $value */
                    function ($value): array {
                        return ['path' => $value];
                    },
                )
        ;

        $screenshotNodeBuilder = $screenshotNodeDefinition->children();
        $screenshotNodeBuilder->scalarNode('path')->isRequired();
        $screenshotNodeBuilder->scalarNode('title')->cannotBeEmpty();
        $screenshotNodeBuilder->scalarNode('description')->cannotBeEmpty();
    }

    private function addOptionalAuthorsList(ArrayNodeDefinition $rootNodeDefinition): void
    {
        $authorsNodeDefinition = $rootNodeDefinition->children()->arrayNode('authors');
        $authorsNodeDefinition
            ->requiresAtLeastOneElement()
            ->performNoDeepMerging()
        ;

        /** @var ArrayNodeDefinition $authorNodeDefinition */
        $authorNodeDefinition = $authorsNodeDefinition->arrayPrototype();
        $authorNodeDefinition
            ->validate()
                ->ifTrue(
                    /** @param mixed $author */
                    function ($author): bool {
                        return [] === $author;
                    },
                )
                ->thenInvalid('Author cannot be empty!')
        ;

        $authorNodeBuilder = $authorNodeDefinition->children();
        $authorNodeBuilder->scalarNode('name')->cannotBeEmpty();
        $authorNodeBuilder->scalarNode('email')->cannotBeEmpty();
        $authorNodeBuilder->scalarNode('homepage')->cannotBeEmpty();
        $authorNodeBuilder->scalarNode('role')->cannotBeEmpty();
    }
}
