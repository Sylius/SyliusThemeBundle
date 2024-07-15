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

namespace Sylius\Bundle\ThemeBundle\Model;

class Theme implements ThemeInterface
{
    protected string $name;

    protected string $path;

    /** @var string|null */
    protected $title;

    /** @var string|null */
    protected $description;

    /** @var array|ThemeAuthor[] */
    protected $authors = [];

    /** @var array|ThemeInterface[] */
    protected $parents = [];

    /** @var array|ThemeScreenshot[] */
    protected $screenshots = [];

    public function __construct(string $name, string $path)
    {
        $this->assertNameIsValid($name);

        $this->name = $name;
        $this->path = $path;
    }

    public function __toString(): string
    {
        return $this->title ?? $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function addAuthor(ThemeAuthor $author): void
    {
        $this->authors[] = $author;
    }

    public function removeAuthor(ThemeAuthor $author): void
    {
        $this->authors = array_filter($this->authors, function ($currentAuthor) use ($author) {
            return $currentAuthor !== $author;
        });
    }

    public function getParents(): array
    {
        return $this->parents;
    }

    public function addParent(ThemeInterface $theme): void
    {
        $this->parents[] = $theme;
    }

    public function removeParent(ThemeInterface $theme): void
    {
        $this->parents = array_filter($this->parents, function ($currentTheme) use ($theme) {
            return $currentTheme !== $theme;
        });
    }

    public function getScreenshots(): array
    {
        return $this->screenshots;
    }

    public function addScreenshot(ThemeScreenshot $screenshot): void
    {
        $this->screenshots[] = $screenshot;
    }

    public function removeScreenshot(ThemeScreenshot $screenshot): void
    {
        $this->screenshots = array_filter($this->screenshots, function ($currentScreenshot) use ($screenshot) {
            return $currentScreenshot !== $screenshot;
        });
    }

    private function assertNameIsValid(string $name): void
    {
        $pattern = '/^[a-zA-Z0-9\-]+\/[a-zA-Z0-9\-]+$/';
        if (false === (bool) preg_match($pattern, $name)) {
            throw new \InvalidArgumentException(sprintf(
                'Given name "%s" does not match regular expression "%s".',
                $name,
                $pattern,
            ));
        }
    }
}
