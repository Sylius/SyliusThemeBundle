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

namespace Sylius\Bundle\ThemeBundle\Translation\Provider\Resource;

use Sylius\Bundle\ThemeBundle\Translation\Resource\TranslationResource;
use Sylius\Bundle\ThemeBundle\Translation\Resource\TranslationResourceInterface;

final class SymfonyTranslatorResourceProvider implements TranslatorResourceProviderInterface
{
    /** @var TranslationResourceInterface[] */
    private array $resources = [];

    private array $resourcesLocales = [];

    private array $filepaths;

    public function __construct(array $filepaths = [])
    {
        $this->filepaths = $filepaths;
    }

    public function getResources(): array
    {
        $this->initializeIfNeeded();

        return $this->resources;
    }

    public function getResourcesLocales(): array
    {
        $this->initializeIfNeeded();

        return $this->resourcesLocales;
    }

    private function initializeIfNeeded(): void
    {
        if (empty($this->resources) || empty($this->resourcesLocales)) {
            $resource = $this->createTranslationResource()->getReturn();

            $this->resources[] = $resource;
            $this->resourcesLocales[] = $resource->getLocale();
            $this->resourcesLocales = array_unique($this->resourcesLocales);
            $this->filepaths = [];
        }
    }

    private function createTranslationResource(): \Generator
    {
        foreach ($this->filepaths as $filepath) {
            yield new TranslationResource($filepath);
        }
    }
}
