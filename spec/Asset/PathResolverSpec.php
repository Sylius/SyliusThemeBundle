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
use Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class PathResolverSpec extends ObjectBehavior
{
    function it_implements_path_resolver_interface(): void
    {
        $this->shouldImplement(PathResolverInterface::class);
    }

    function it_returns_modified_path_if_its_referencing_bundle_asset(ThemeInterface $theme): void
    {
        $theme->getName()->willReturn('theme/name');

        $this->resolve('bundles/acme/asset.min.js', '/', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('bundles/acme/asset.min.js', '', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');

        $this->resolve('/root/bundles/acme/asset.min.js', '/root', $theme)->shouldReturn('/root/_themes/theme/name/bundles/acme/asset.min.js');
        $this->resolve('/root/bundles/acme/asset.min.js', '/root/', $theme)->shouldReturn('/root/_themes/theme/name/bundles/acme/asset.min.js');
    }

    function it_returns_modified_path_if_its_referencing_root_asset(ThemeInterface $theme): void
    {
        $theme->getName()->willReturn('theme/name');

        $this->resolve('asset.min.js', '/', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');
        $this->resolve('asset.min.js', '', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');

        $this->resolve('/root/asset.min.js', '/root', $theme)->shouldReturn('/root/_themes/theme/name/asset.min.js');
        $this->resolve('/root/asset.min.js', '/root/', $theme)->shouldReturn('/root/_themes/theme/name/asset.min.js');
    }

    function it_prepends_theme_path_if_the_base_path_is_not_found(ThemeInterface $theme): void
    {
        $theme->getName()->willReturn('theme/name');

        $this->resolve('asset.min.js', '/lol', $theme)->shouldReturn('/_themes/theme/name/asset.min.js');
        $this->resolve('bundles/acme/asset.min.js', '/lol', $theme)->shouldReturn('/_themes/theme/name/bundles/acme/asset.min.js');
    }
}
