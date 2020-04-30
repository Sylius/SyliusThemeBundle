## Configuration sources

To discover themes that are defined in the application, ThemeBundle uses configuration sources.

### Filesystem configuration source

**Filesystem** configuration source loads theme definitions from files placed under specified directories.

By default it seeks for `composer.json` files that exists under `%kernel.project_dir%/themes` directory, which
usually is resolved to `<Project>/themes`.

#### Configuration reference

```yaml
sylius_theme:
    sources:
        filesystem:
            enabled: false
            filename: composer.json
            scan_depth: 1
            directories:
                - "%kernel.project_dir%/themes"
```
    
### Test configuration source

**Test** configuration source provides an interface that can be used to add, remove and access themes in test environment.
They are stored in the cache directory and if used with Behat, they are persisted across steps but not across scenarios.

#### Configuration reference

This source does not have any configuration options. To enable it, use the following configuration:

```yaml
    sylius_theme:
        sources:
            test: ~
```

#### Usage

In order to use tests, have a look at `TestThemeConfigurationManager` class. You can:

 - add a theme: `add(array $configuration): void`
 - remove a theme: `remove(string $themeName): void`
 - remove all themes: `clear(): void`

### Creating custom configuration source

If your needs can't be fulfilled by built-in configuration sources, you can create a custom one in a few minutes!

#### Configuration provider

The configuration provider contains the core logic of themes configurations retrieval.

It requires only one method - `getConfigurations()` which receives no arguments and returns an array of configuration arrays.

```php
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface;

final class CustomConfigurationProvider implements ConfigurationProviderInterface
{
    public function getConfigurations(): array
    {
        return [
            [
                'name' => 'theme/name',
                'path' => '/theme/path',
                'title' => 'Theme title',
            ],
        ];
    }
}
```

#### Configuration source factory

The configuration source factory is the glue between your brand new configuration provider and ThemeBundle.

It provides an easy way to allow customization of your configuration source and defines how the configuration
provider is constructed.

```php
use Sylius\Bundle\ThemeBundle\Configuration\ConfigurationSourceFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class CustomConfigurationSourceFactory implements ConfigurationSourceFactoryInterface
{
    public function buildConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('option')
        ;
    }

    public function initializeSource(ContainerBuilder $container, array $config)
    {
        return new Definition(CustomConfigurationProvider::class, [
            $config['option'], // pass an argument configured by end user to configuration provider
        ]);
    }

    public function getName(): string
    {
        return 'custom';
    }
}
```

Try not to define any public services in the container inside `initializeSource()` - it will prevent Symfony from
cleaning it up and will remain in the compiled container even if not used.

The last step is to tell ThemeBundle to use the source factory defined before. It can be done in your bundle definition:

```php
use Sylius\Bundle\ThemeBundle\DependencyInjection\SyliusThemeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AcmeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var SyliusThemeExtension $themeExtension */
        $themeExtension = $container->getExtension('sylius_theme');
        $themeExtension->addConfigurationSourceFactory(new CustomConfigurationSourceFactory());
    }
}
```

#### Usage

Configuration source is set up, it will start providing themes configurations as soon as it is enabled in ThemeBundle:

```yaml
sylius_theme:
    sources:
        custom: ~
```

**[Go back to the documentation's index](index.md)**
