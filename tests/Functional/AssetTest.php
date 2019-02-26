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

use Psr\Container\ContainerInterface;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstallerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
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

        $webDirectory = $this->createWebDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($webDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/:Asset:assetsTest.txt.twig');
        $lines = explode("\n", $crawler->text());

        $this->assertFileContent($lines, $webDirectory);
    }

    /**
     * @test
     * @dataProvider getSymlinkMasks
     */
    public function it_updates_dumped_assets_if_they_are_modified(int $symlinkMask): void
    {
        $client = self::createClient();

        $webDirectory = $this->createWebDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($webDirectory, $symlinkMask);

        $themeAssetPath = __DIR__ . '/../Fixtures/themes/FirstTestTheme/TestBundle/public/theme_asset.txt';
        $themeAssetContent = file_get_contents($themeAssetPath);

        try {
            file_put_contents($themeAssetPath, 'Theme asset modified' . \PHP_EOL);

            $this->getThemeAssetsInstaller($client)->installAssets($webDirectory, $symlinkMask);

            $crawler = $client->request('GET', '/template/:Asset:modifiedAssetsTest.txt.twig');
            $lines = explode("\n", $crawler->text());

            $this->assertFileContent($lines, $webDirectory);
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

        $webDirectory = $this->createWebDirectory();

        $this->getThemeAssetsInstaller($client)->installAssets($webDirectory, $symlinkMask);
        $this->getThemeAssetsInstaller($client)->installAssets($webDirectory, $symlinkMask);

        $crawler = $client->request('GET', '/template/:Asset:assetsTest.txt.twig');
        $lines = explode("\n", $crawler->text());

        $this->assertFileContent($lines, $webDirectory);
    }

    public function getSymlinkMasks(): array
    {
        return [
            [AssetsInstallerInterface::RELATIVE_SYMLINK],
            [AssetsInstallerInterface::SYMLINK],
            [AssetsInstallerInterface::HARD_COPY],
        ];
    }

    private function createWebDirectory(): string
    {
        $webDirectory = self::$kernel->getCacheDir() . '/web';
        if (!is_dir($webDirectory)) {
            mkdir($webDirectory, 0777, true);
        }

        chdir($webDirectory);

        return $webDirectory;
    }

    private function assertFileContent($lines, $webDirectory): void
    {
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $this->assertStringContainsString(':', $line);

            [$expectedText, $assetFile] = explode(': ', $line);

            $contents = (string) file_get_contents($webDirectory . $assetFile);

            $this->assertEquals($expectedText, trim($contents));
        }
    }

    private function getThemeAssetsInstaller(Client $client): AssetsInstallerInterface
    {
        /** @var ContainerInterface $container */
        $container = $client->getContainer();

        $themeAssetsInstaller = $container->get('sylius.theme.asset.assets_installer');

        assert($themeAssetsInstaller instanceof AssetsInstallerInterface);

        return $themeAssetsInstaller;
    }
}
