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

namespace Sylius\Bundle\ThemeBundle\Tests\Functional;

use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstallerInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssetTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider getSymlinkMasks
     */
    public function it_dumps_assets(int $symlinkMask): void
    {
        $client = self::createClient();

        $publicDirectory = $this->createPublicDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/Asset/assetsTest.txt.twig');
        $lines = explode(', ', $crawler->text());

        $this->assertFileContent($lines, $publicDirectory);
    }

    /**
     * @test
     */
    public function it_updates_dumped_assets_if_they_are_modified(): void
    {
        $client = self::createClient();

        $publicDirectory = $this->createPublicDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, AssetsInstallerInterface::HARD_COPY);

        $themeAssetPath = __DIR__ . '/../Fixtures/themes/FirstTestTheme/public/bundles/test/theme_asset.txt';
        $themeAssetContent = file_get_contents($themeAssetPath);

        try {
            file_put_contents($themeAssetPath, 'Theme asset modified' . \PHP_EOL);

            $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, AssetsInstallerInterface::HARD_COPY);

            $crawler = $client->request('GET', '/template/Asset/modifiedAssetsTest.txt.twig');
            $lines = explode(', ', $crawler->text());

            $this->assertFileContent($lines, $publicDirectory);
        } finally {
            file_put_contents($themeAssetPath, $themeAssetContent);
        }
    }

    /**
     * @test
     * @dataProvider getSymlinkMasks
     */
    public function it_dumps_assets_correctly_even_if_nothing_has_changed(int $symlinkMask): void
    {
        $client = self::createClient();

        $publicDirectory = $this->createPublicDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, $symlinkMask);
        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/Asset/assetsTest.txt.twig');
        $lines = explode(', ', $crawler->text());

        $this->assertFileContent($lines, $publicDirectory);
    }

    /**
     * @test
     * @dataProvider getSymlinkMasks
     */
    public function it_falls_back_to_regular_assets_if_themed_not_found(int $symlinkMask): void
    {
        $client = self::createClient();

        $publicDirectory = $this->createPublicDirectory();
        file_put_contents($publicDirectory . 'public_asset.txt', 'Public asset');

        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/Asset/publicAssetsTest.txt.twig');
        $lines = explode(', ', $crawler->text());

        $this->assertFileContent($lines, $publicDirectory);
    }

    /**
     * @test
     * @group legacy
     * @dataProvider getSymlinkMasks
     */
    public function it_dumps_legacy_assets(int $symlinkMask): void
    {
        $client = self::createClient();

        $publicDirectory = $this->createPublicDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($publicDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/Asset/legacyAssetsTest.txt.twig');
        $lines = explode(', ', $crawler->text());

        $this->assertFileContent($lines, $publicDirectory);
    }

    public function getSymlinkMasks(): array
    {
        return [
            [AssetsInstallerInterface::RELATIVE_SYMLINK],
            [AssetsInstallerInterface::SYMLINK],
            [AssetsInstallerInterface::HARD_COPY],
        ];
    }

    private function createPublicDirectory(): string
    {
        $publicDirectory = self::$kernel->getCacheDir() . '/public/';

        if (is_dir($publicDirectory)) {
            (new Filesystem())->remove($publicDirectory);
        }

        if (!is_dir($publicDirectory)) {
            mkdir($publicDirectory, 0777, true);
        }

        chdir($publicDirectory);

        return $publicDirectory;
    }

    private function assertFileContent(array $lines, string $publicDirectory): void
    {
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $this->assertStringContainsString(':', $line);

            [$expectedText, $assetFile] = explode(': ', $line);

            $contents = (string) file_get_contents($publicDirectory . trim($assetFile));

            $this->assertEquals($expectedText, trim($contents));
        }
    }

    private function getThemeAssetsInstaller(KernelBrowser $client): AssetsInstallerInterface
    {
        $themeAssetsInstaller = static::getContainer()->get(AssetsInstallerInterface::class);

        assert($themeAssetsInstaller instanceof AssetsInstallerInterface);

        return $themeAssetsInstaller;
    }
}
