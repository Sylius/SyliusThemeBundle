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

use Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @deprecated Deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.
 */
final class LegacyTranslationFilesFinder implements TranslationFilesFinderInterface
{
    private FinderFactoryInterface $finderFactory;

    public function __construct(FinderFactoryInterface $finderFactory)
    {
        @trigger_error(sprintf(
            '"%s" is deprecated since Sylius/ThemeBundle 2.0 and will be removed in 3.0.',
            self::class,
        ), \E_USER_DEPRECATED);

        $this->finderFactory = $finderFactory;
    }

    public function findTranslationFiles(string $path): array
    {
        $themeFiles = $this->getFiles($path);

        $translationsFiles = [];
        foreach ($themeFiles as $themeFile) {
            $themeFilepath = (string) $themeFile;

            if (!$this->isTranslationFile($themeFilepath)) {
                continue;
            }

            $translationsFiles[] = $themeFilepath;
        }

        return $translationsFiles;
    }

    /**
     * @return iterable|SplFileInfo[]
     */
    private function getFiles(string $path): iterable
    {
        $finder = $this->finderFactory->create();

        $finder
            ->ignoreUnreadableDirs()
            ->in($path)
        ;

        return $finder;
    }

    private function isTranslationFile(string $file): bool
    {
        return false !== strpos($file, 'translations' . \DIRECTORY_SEPARATOR) &&
            (bool) preg_match('/^[^\.]+?\.[a-zA-Z_]{2,}?\.[a-z0-9]{2,}?$/', basename($file));
    }
}
