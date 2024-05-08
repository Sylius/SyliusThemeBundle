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

namespace Sylius\Bundle\ThemeBundle\Command;

use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsInstallerInterface;
use Sylius\Bundle\ThemeBundle\Asset\Installer\OutputAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command that places themes web assets into a given directory.
 */
final class AssetsInstallCommand extends Command
{
    private AssetsInstallerInterface $assetsInstaller;

    private string $projectDir;

    public function __construct(AssetsInstallerInterface $assetsInstaller, string $projectDir)
    {
        parent::__construct(null);

        $this->assetsInstaller = $assetsInstaller;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            ->setName('sylius:theme:assets:install')
            ->setDefinition([
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory'),
            ])
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs themes web assets under a public web directory')
            ->setHelp($this->getHelpMessage())
        ;
    }

    /**
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->assetsInstaller instanceof OutputAwareInterface) {
            $this->assetsInstaller->setOutput($output);
        }

        $symlinkMask = AssetsInstallerInterface::HARD_COPY;

        if ($input->getOption('symlink')) {
            $symlinkMask = max($symlinkMask, AssetsInstallerInterface::SYMLINK);
        }

        if ($input->getOption('relative')) {
            $symlinkMask = max($symlinkMask, AssetsInstallerInterface::RELATIVE_SYMLINK);
        }

        $this->assetsInstaller->installAssets($this->getTargetDir($input), $symlinkMask);

        return 0;
    }

    private function getTargetDir(InputInterface $input): string
    {
        /** @var string|null $targetDirectory */
        $targetDirectory = $input->getArgument('target');
        $targetDirectory = rtrim((string) $targetDirectory, '/');

        if (!$targetDirectory) {
            $targetDirectory = $this->getPublicDirectory();
        }

        if (!is_dir($targetDirectory)) {
            $targetDirectory = $this->projectDir . '/' . $targetDirectory;

            if (!is_dir($targetDirectory)) {
                throw new InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $targetDirectory));
            }
        }

        return $targetDirectory;
    }

    private function getHelpMessage(): string
    {
        return <<<EOT
The <info>%command.name%</info> command installs theme assets into a given
directory (e.g. the <comment>public</comment> directory).

  <info>php %command.full_name% public</info>

A "themes" directory will be created inside the target directory.

To create a symlink to each theme instead of copying its assets, use the
<info>--symlink</info> option (will fall back to hard copies when symbolic links aren't possible):

  <info>php %command.full_name% public --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

  <info>php %command.full_name% public --symlink --relative</info>

EOT;
    }

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand::getPublicDirectory()
     */
    private function getPublicDirectory(): string
    {
        $defaultPublicDir = 'public';

        $composerFilePath = $this->projectDir . '/composer.json';

        if (!file_exists($composerFilePath)) {
            return $defaultPublicDir;
        }

        $composerConfig = json_decode((string) file_get_contents($composerFilePath), true);

        if (isset($composerConfig['extra']['public-dir'])) {
            return $composerConfig['extra']['public-dir'];
        }

        return $defaultPublicDir;
    }
}
