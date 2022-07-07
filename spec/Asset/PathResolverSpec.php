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

namespace spec\Sylius\Bundle\ThemeBundle\Asset;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class PathResolverSpec extends ObjectBehavior
{
    function let(AssetsProviderInterface $assetsProvider, FilesystemInterface $filesystem): void
    {
        $this->beConstructedWith($assetsProvider, $filesystem);
    }

    function it_implements_path_resolver_interface(): void
    {
        $this->shouldImplement(PathResolverInterface::class);
    }

    function it_returns_modified_path_if_its_referencing_bundle_asset(
        AssetsProviderInterface $assetsProvider,
        FilesystemInterface $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getName()->willReturn('theme/name');

        $assetsProvider->provideDirectoriesForTheme($theme)->willYield(['/src/acme-bundle/Resources/public' => '/bundles/acme', '/src/acme-bundle/public' => '/bundles/acme', '/src/easy-bundle/Resources/public' => '/bundles/easy', '/src/easy-bundle/public' => '/bundles/easy']);

        $filesystem->exists('/src/acme-bundle/Resources/public/asset.min.js')->willReturn(true);
        $filesystem->exists('/src/acme-bundle/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/Resources/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/public/asset.min.js')->willReturn(true);

        $this->resolve('bundles/acme/asset.min.js', '/', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('bundles/acme/asset.min.js', '', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('bundles/easy/asset.min.js', '/', $theme)->shouldReturn('/_themes/theme/name/bundles/easy/asset.min.js');
        $this->resolve('bundles/easy/asset.min.js', '', $theme)->shouldReturn('/_themes/theme/name/bundles/easy/asset.min.js');

        $this->resolve('/root/bundles/acme/asset.min.js', '/root', $theme)->shouldReturn('/root/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('/root/bundles/acme/asset.min.js', '/root/', $theme)->shouldReturn('/root/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('/root/bundles/easy/asset.min.js', '/root', $theme)->shouldReturn('/root/_themes/theme/name/bundles/easy/asset.min.js');
        $this->resolve('/root/bundles/easy/asset.min.js', '/root/', $theme)->shouldReturn('/root/_themes/theme/name/bundles/easy/asset.min.js');
    }

    function it_returns_modified_path_if_its_referencing_root_asset(
        AssetsProviderInterface $assetsProvider,
        FilesystemInterface $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getName()->willReturn('theme/name');

        $assetsProvider->provideDirectoriesForTheme($theme)->willYield(['/src/theme/public' => '/']);

        $filesystem->exists('/src/theme/public/asset.min.js')->willReturn(true);

        $this->resolve('asset.min.js', '/', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');
        $this->resolve('asset.min.js', '', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');

        $this->resolve('/root/asset.min.js', '/root', $theme)->shouldReturn('/root/_themes/theme/name/asset.min.js');
        $this->resolve('/root/asset.min.js', '/root/', $theme)->shouldReturn('/root/_themes/theme/name/asset.min.js');
    }

    function it_prepends_theme_path_if_the_base_path_is_not_found(
        AssetsProviderInterface $assetsProvider,
        FilesystemInterface $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getName()->willReturn('theme/name');

        $assetsProvider->provideDirectoriesForTheme($theme)->willYield([
            '/src/acme-bundle/Resources/public' => '/bundles/acme',
            '/src/acme-bundle/public' => '/bundles/acme',
            '/src/easy-bundle/Resources/public' => '/bundles/easy',
            '/src/easy-bundle/public' => '/bundles/easy',
            '/src/theme/public' => '/',
        ]);

        $filesystem->exists('/src/acme-bundle/Resources/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/acme-bundle/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/Resources/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/theme/public/asset.min.js')->willReturn(true);

        $filesystem->exists('/src/acme-bundle/Resources/public/asset.min.js')->willReturn(true);
        $filesystem->exists('/src/acme-bundle/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/Resources/public/asset.min.js')->willReturn(false);
        $filesystem->exists('/src/easy-bundle/public/asset.min.js')->willReturn(true);
        $filesystem->exists('/src/theme/public/bundles/acme/asset.min.js')->willReturn(false);

        $this->resolve('asset.min.js', '/lol', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');
        $this->resolve('bundles/acme/asset.min.js', '/lol', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('bundles/easy/asset.min.js', '/lol', $theme)->shouldReturn('/_themes/theme/name/bundles/easy/asset.min.js');
    }

    function it_fallbacks_to_default_path_if_could_not_find_themed_asset(
        AssetsProviderInterface $assetsProvider,
        FilesystemInterface $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getName()->willReturn('theme/name');

        $assetsProvider->provideDirectoriesForTheme($theme)->willYield([
            '/src/acme-bundle/Resources/public' => '/bundles/acme',
            '/src/acme-bundle/public' => '/bundles/acme',
            '/src/theme/public' => '/',
        ]);

        $filesystem->exists(Argument::any())->willReturn(false);

        $this->resolve('asset.min.js', '/', $theme)->shouldReturn('asset.min.js');
        $this->resolve('asset.min.js', '', $theme)->shouldReturn('asset.min.js');
        $this->resolve('/root/asset.min.js', '/root', $theme)->shouldReturn('/root/asset.min.js');
        $this->resolve('/root/asset.min.js', '/root/', $theme)->shouldReturn('/root/asset.min.js');
    }
}
