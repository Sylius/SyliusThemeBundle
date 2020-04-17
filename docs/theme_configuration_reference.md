## Theme configuration reference

```json
{
    "name": "vendor/sylius-theme",
    "title": "Great Sylius theme!",
    "description": "Optional description",
    "authors": [
        {
            "name": "Kamil Kokot",
            "email": "kamil@kokot.me",
            "homepage": "https://kamilkokot.com",
            "role": "Developer"
        }
    ],
    "parents": [
        "vendor/common-sylius-theme",
        "another-vendor/not-so-cool-looking-sylius-theme"
    ]
}
```

Theme configuration was meant to be mixed with the one from Composer. Fields `name`, `description` and
`authors` are shared between these by default. To use different values for Composer & ThemeBundle,
have a look below.

### Composer integration

```json
{
    "name": "vendor/sylius-theme",
    "type": "sylius-theme",
    "description": "Composer package description",
    "authors": [
        {
            "name": "Kamil Kokot"
        }
    ],
    "extra": {
        "sylius-theme": {
            "description": "Theme description",
            "parents": [
                "vendor/other-sylius-theme"
            ]
        }
    }
}
```

By configuring Composer package along with theme we do not have to duplicate fields like `name` or `authors`,
but we are free to overwrite them in any time, just like the `description` field in example above.
The theme configuration is complementary to the Composer configuration and results in perfectly valid `composer.json`.

**[Go back to the documentation's index](index.md)**
