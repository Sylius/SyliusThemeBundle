## Your first theme

This tutorial assumes that you use the [filesystem configuration source](configuration_sources.md#filesystem-configuration-source).
Make sure it's enabled with the default options:

```yaml
sylius_theme:
    sources:
        filesystem: ~
```

### Themes location and definition

Private themes should be added to `themes` directory by default. Every theme should have a default configuration
located in `composer.json` file. The only required parameter is `name`, but it is worth to define other ones
([have a look at theme configuration reference](theme_configuration_reference.md)).

```json
{
  "name": "vendor/default-theme"
}
```

When adding or removing a theme, it's necessary to rebuild the container (same as adding new translation files in Symfony) by clearing the cache (`bin/console cache:clear`).

### Theme structure

Themes can override and add both bundle resources and app resources. When your theme configuration is in `SampleTheme/composer.json`,
app resources should be located at `SampleTheme/templates` for templates, `SampleTheme/translations` for translations and `SampleTheme/public` for assets.
To override a specific bundle's template (eg. `FOSUserBundle`), put it in `SampleTheme/templates/bundles/FOSUserBundle` directory.

```
AcmeTheme
├── composer.json
├── public
│   └── asset.jpg
├── templates
│   ├── bundles
│   │   └── AcmeBundle
│   │       └── bundleTemplate.html.twig
|   └── template.html.twig
└── translations
    └── messages.en.yml
```

### Enabling themes

Themes are enabled on the runtime and uses the theme context to define which one is currently used.
There are two ways to enable your theme:

#### Custom theme context

Implement `Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface`, register it as a service and replace the default
theme context with the new one by changing ThemeBundle configuration:

```yaml
sylius_theme:
    context: acme.theme_context # theme context service id
```

#### Request listener and settable theme context

Create an event listener and register it as listening for `kernel.request` event.

```php
use Sylius\Bundle\ThemeBundle\Context\SettableThemeContext;
use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[AsEventListener(
    event: RequestEvent::class,
)]
final class ThemeRequestListener
{
    /** @var ThemeRepositoryInterface */
    private $themeRepository;

    /** @var SettableThemeContext */
    private $themeContext;

    public function __construct(ThemeRepositoryInterface $themeRepository, SettableThemeContext $themeContext)
    {
        $this->themeRepository = $themeRepository;
        $this->themeContext = $themeContext;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $this->themeContext->setTheme(
            $this->themeRepository->findOneByName('sylius/cool-theme')
        );
    }
}
```

### Theme assets

When creating a new theme, any templates not in your own theme are taken from the default SyliusShopBundle views - otherwise you'd need to copy all the files.
But watch out! Assets like javascript resources are not loaded this way. If you install some assets you will need to link them to
your theme files by using this command:

```bash
php bin/console sylius:theme:assets:install
```

**[Go back to the documentation's index](index.md)**
