## Important changes

`SyliusThemeBundle` changes the way vanilla Symfony works a lot. Templates and translations will never behave
the same as they were.

### Templates

Changed loading order (priority descending):

- Application templates:
    - `<Theme>/templates` **(NEW!)**
    - `<Project>/templates`
- Bundle templates:
    - `<Theme>/templates/bundles/<Bundle name>` **(NEW!)**
    - `<Project>/templates/bundles/<Bundle name>`
    - `<Bundle>/Resources/views`

### Translations

Changed loading order (priority descending):

- `<Theme>/translations` **(NEW!)**
- `<Project>/translations`
- `<Bundle>/Resources/translations`

### Assets

Theme assets are installed by `sylius:theme:assets:install` command, which is supplementary for and should be used after `assets:install`.

The command run with `--symlink` or `--relative` parameters creates symlinks for every installed asset file,
not for entire asset directory (eg. if `AcmeBundle/Resources/public/asset.js` exists, it creates symlink `public/bundles/acme/asset.js`
leading to `AcmeBundle/Resources/public/asset.js` instead of symlink `public/bundles/acme/` leading to `AcmeBundle/Resources/public/`).
When you create a new asset or delete an existing one, it is required to rerun this command to apply changes (just as the hard copy option works).

### Assetic

Nothing has changed, `ThemeBundle` is not and will not be integrated with `Assetic`.

**[Go back to the documentation's index](index.md)**
