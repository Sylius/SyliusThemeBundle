## Webpack Encore integration

Themes can ship assets built with [Webpack Encore](https://symfony.com/doc/current/frontend.html).
Each theme becomes an entry of a dedicated Encore build, named after the theme, and the entry of the current theme
is rendered with Twig functions.

### Configuration

The integration requires [WebpackEncoreBundle](https://github.com/symfony/webpack-encore-bundle) and is disabled by default:

```yaml
sylius_theme:
    webpack_encore: true

webpack_encore:
    builds:
        themes: "%kernel.project_dir%/public/build/themes"
```

### Building theme assets

Create an entrypoint in your theme, e.g. `themes/CrimsonTheme/assets/entrypoint.js`, and add an entry for each theme
to a separate Encore build. The `src/Resources/webpack` module of this bundle locates themes the same way
the filesystem configuration source does, using their `composer.json` files:

```js
// webpack.config.js
const Encore = require('@symfony/webpack-encore');
const SyliusTheme = require('./vendor/sylius/theme-bundle/src/Resources/webpack');

const { entries, aliases } = SyliusTheme.getThemesAssets(__dirname, {
    directories: ['themes'], // relative to the project directory
    scanDepth: 1,
    assetsDir: 'assets', // relative to the theme directory
    entrypoint: 'entrypoint.js', // relative to the assets directory
});

Encore
    .setOutputPath('public/build/themes/')
    .setPublicPath('/build/themes')
    .addAliases(aliases)
    .addEntries(entries);

const themesConfig = Encore.getWebpackConfig();
themesConfig.name = 'themes';

module.exports = themesConfig;
```

* Each entry is named after the theme, e.g. `acme/crimson-theme`.
* The assets directory of each theme is available under the `@sylius-theme/<theme name>` alias.
* The build fails if two themes have the same name.

### Rendering theme assets

Pass the name of the Encore build to the Twig functions:

```twig
{{ sylius_theme_encore_entry_link_tags('themes') }}
{{ sylius_theme_encore_entry_script_tags('themes') }}
```

They accept the same `packageName` and `attributes` arguments as `encore_entry_link_tags()` and `encore_entry_script_tags()`.

Both functions follow the [theme inheritance](theme_inheritance.md): they render the entry of the current theme
or, if it has none, the entry of its closest parent that has one. Nothing is rendered when there is no current theme,
no theme in the hierarchy has an entry or the build has not been compiled yet.

### Extending parent theme assets

A child theme can extend the assets of its parent by importing them with the alias:

```js
// themes/ChildTheme/assets/entrypoint.js
import '@sylius-theme/acme/crimson-theme/entrypoint';
import './styles/overrides.scss';
```

**[Go back to the documentation's index](index.md)**
