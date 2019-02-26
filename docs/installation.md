## Installation

```bash
composer require sylius/theme-bundle
```

## Adding required bundles to the kernel

You need to enable the bundle inside the kernel, usually at the end of bundle list.

```php
# config/bundles.php

return [
        \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
        \Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],

        // Other bundles...
        \Sylius\Bundle\ThemeBundle\SyliusThemeBundle::class => ['all' => true],
];
```

**Please register the bundle after `FrameworkBundle`. This is important as we override default templating, translation and assets logic.**

## Configuring bundle
------------------

In order to store your themes metadata in the filesystem, add the following configuration:

```yaml
sylius_theme:
    sources:
        filesystem: ~
```

**[Go back to the documentation's index](index.md)**
