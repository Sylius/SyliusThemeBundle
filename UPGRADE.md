## UPGRADE FROM `1.x` TO `2.0`

* Renamed the following services:

    * `sylius.collector.theme` replaced with `Sylius\Bundle\ThemeBundle\Collector\ThemeCollector`
    * `sylius.theme.asset.assets_installer` replaced with `Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstaller`
    * `sylius.theme.asset.path_resolver` replaced with `Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface`
    * `sylius.theme.filesystem` replaced with `Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface`
    * `sylius.theme.finder_factory` replaced with `Sylius\Bundle\ThemeBundle\Factory\FinderFactoryInterface`
    * `sylius.theme.form.type.theme_choice` replaced with `Sylius\Bundle\ThemeBundle\Form\Type\ThemeChoiceType`
    * `sylius.theme.form.type.theme_name_choice` replaced with `Sylius\Bundle\ThemeBundle\Form\Type\ThemeNameChoiceType`
    * `sylius.theme.locator.application_resource` replaced with `Sylius\Bundle\ThemeBundle\Twig\Locator\ApplicationTemplateLocator`
    * `sylius.theme.locator.bundle_resource` replaced with `Sylius\Bundle\ThemeBundle\Twig\Locator\BundleTemplateLocator` and `Sylius\Bundle\ThemeBundle\Twig\Locator\NamespacedTemplateLocator`
    * `sylius.theme.locator.resource` replaced with `Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface`
    * `sylius.theme.templating.locator` replaced with `Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface`
    * `sylius.theme.translation.files_finder` replaced with `Sylius\Bundle\ThemeBundle\Translation\Finder\TranslationFilesFinderInterface`
    * `sylius.theme.translation.loader_provider` replaced with `Sylius\Bundle\ThemeBundle\Translation\Provider\Loader\TranslatorLoaderProviderInterface`
    * `sylius.theme.translation.resource_provider` replaced with `Sylius\Bundle\ThemeBundle\Translation\Provider\Resource\TranslatorResourceProviderInterface`
    
* Deprecated the following services:

    * `sylius.context.theme` superseded by `Sylius\Bundle\ThemeBundle\Context\SettableThemeContext` instead
    * `sylius.factory.theme_author` superseded by `Sylius\Bundle\ThemeBundle\Factory\ThemeAuthorFactoryInterface` instead
    * `sylius.factory.theme_screenshot` superseded by `Sylius\Bundle\ThemeBundle\Factory\ThemeScreenshotFactoryInterface` instead
    * `sylius.factory.theme` superseded by `Sylius\Bundle\ThemeBundle\Factory\ThemeFactoryInterface` instead
    * `sylius.repository.theme` superseded by `Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface` instead
    * `sylius.theme.circular_dependency_checker` superseded by `Sylius\Bundle\ThemeBundle\Loader\CircularDependencyCheckerInterface` instead
    * `sylius.theme.configuration.processor` superseded by `Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProcessorInterface` instead
    * `sylius.theme.configuration.provider` superseded by `Sylius\Bundle\ThemeBundle\Configuration\ConfigurationProviderInterface` instead
    * `sylius.theme.configuration` superseded by `Sylius\Bundle\ThemeBundle\Configuration\ThemeConfiguration` instead
    * `sylius.theme.context.settable` superseded by `Sylius\Bundle\ThemeBundle\Context\SettableThemeContext` instead
    * `sylius.theme.hierarchy_provider` superseded by `Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface` instead
    * `sylius.theme.loader` superseded by `Sylius\Bundle\ThemeBundle\Loader\ThemeLoaderInterface` instead

* Removed the following services:

    * `sylius.theme.hydrator`
    * `sylius.theme.templating.cache.clearer`
    * `sylius.theme.templating.cache.warmer`
    * `sylius.theme.templating.file_locator`
