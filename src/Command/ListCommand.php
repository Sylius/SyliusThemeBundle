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

use Sylius\Bundle\ThemeBundle\Repository\ThemeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListCommand extends Command
{
    private ThemeRepositoryInterface $themeRepository;

    public function __construct(ThemeRepositoryInterface $themeRepository)
    {
        parent::__construct(null);

        $this->themeRepository = $themeRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('sylius:theme:list')
            ->setDescription('Shows list of detected themes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $themes = $this->themeRepository->findAll();

        if (0 === count($themes)) {
            $output->writeln('<error>There are no themes.</error>');

            return 0;
        }

        $output->writeln('<question>Successfully loaded themes:</question>');

        $table = new Table($output);
        $table->setHeaders(['Title', 'Name', 'Path']);

        foreach ($themes as $theme) {
            $table->addRow([$theme->getTitle(), $theme->getName(), $theme->getPath()]);
        }

        $table->setStyle('borderless');
        $table->render();

        return 0;
    }
}
