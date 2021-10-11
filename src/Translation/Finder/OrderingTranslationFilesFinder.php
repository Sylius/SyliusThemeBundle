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

namespace Sylius\Bundle\ThemeBundle\Translation\Finder;

final class OrderingTranslationFilesFinder implements TranslationFilesFinderInterface
{
    private TranslationFilesFinderInterface $translationFilesFinder;

    public function __construct(TranslationFilesFinderInterface $translationFilesFinder)
    {
        $this->translationFilesFinder = $translationFilesFinder;
    }

    public function findTranslationFiles(string $path): array
    {
        $files = $this->translationFilesFinder->findTranslationFiles($path);

        usort($files, static function (string $firstFile, string $secondFile) use ($path): int {
            $firstFile = str_replace($path, '', $firstFile);
            $secondFile = str_replace($path, '', $secondFile);

            return (int) strpos($firstFile, 'translations') <=> (int) strpos($secondFile, 'translations');
        });

        return $files;
    }
}
