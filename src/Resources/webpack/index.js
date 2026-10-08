/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const fs = require('fs');
const path = require('path');

const THEME_ALIAS_PREFIX = '@sylius-theme/';
const THEME_NAME_PATTERN = /^[a-zA-Z0-9-]+\/[a-zA-Z0-9-]+$/;

function locateThemeConfigurationFiles(directory, depth, filename) {
    if (!fs.existsSync(directory) || !fs.statSync(directory).isDirectory()) {
        return [];
    }

    const configurationFile = path.join(directory, filename);
    const files = fs.existsSync(configurationFile) ? [configurationFile] : [];

    if (depth === 0) {
        return files;
    }

    return fs.readdirSync(directory, { withFileTypes: true })
        .filter((entry) => !entry.name.startsWith('.') && (entry.isDirectory() || entry.isSymbolicLink()))
        .reduce((found, entry) => [...found, ...locateThemeConfigurationFiles(path.join(directory, entry.name), depth - 1, filename)], files);
}

function readThemeName(configurationFile) {
    try {
        const configuration = JSON.parse(fs.readFileSync(configurationFile, 'utf8'));

        return configuration.extra?.['sylius-theme']?.name ?? configuration.name ?? null;
    } catch {
        return null;
    }
}

function locateThemes(rootDir, { directories = ['themes'], scanDepth = 1, filename = 'composer.json' } = {}) {
    const themes = new Map();

    directories
        .map((directory) => path.resolve(rootDir, directory))
        .flatMap((directory) => locateThemeConfigurationFiles(directory, scanDepth, filename))
        .forEach((configurationFile) => {
            const name = readThemeName(configurationFile);
            if (name === null || !THEME_NAME_PATTERN.test(name)) {
                return;
            }

            const themePath = fs.realpathSync(path.dirname(configurationFile));
            if (themes.has(name) && themes.get(name) !== themePath) {
                throw new Error(`Theme "${name}" is defined more than once: in "${themes.get(name)}" and in "${themePath}". Theme names must be unique.`);
            }

            themes.set(name, themePath);
        });

    return themes;
}

function getThemesAssets(rootDir, { assetsDir, entrypoint = 'entrypoint.js', ...options }) {
    const themes = locateThemes(rootDir, options);

    const entries = Object.fromEntries([...themes]
        .map(([name, themePath]) => [name, path.join(themePath, assetsDir, entrypoint)])
        .filter(([, file]) => fs.existsSync(file)));

    const aliases = Object.fromEntries([...themes]
        .map(([name, themePath]) => [`${THEME_ALIAS_PREFIX}${name}`, path.join(themePath, assetsDir)])
        .filter(([, themeAssetsDir]) => fs.existsSync(themeAssetsDir)));

    return { entries, aliases };
}

module.exports = {
    THEME_ALIAS_PREFIX,
    getThemesAssets,
    locateThemes
};
