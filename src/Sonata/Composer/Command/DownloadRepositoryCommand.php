<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class DownloadRepositoryCommand
 *
 * @package Sonata\Composer\Command
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DownloadRepositoryCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('download:repository')
            ->setDescription('Download the a git repository')
            ->addArgument('repository', InputArgument::REQUIRED, 'The GIT url of the repository')
            ->addArgument('destination', InputArgument::REQUIRED, 'The destination folder, must be empty');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $git = $this->getApplication()->getGitExecutable();

        $cmd = sprintf("%s clone %s %s", $git, $input->getArgument('repository'), $input->getArgument('destination'));

        $output->writeln(sprintf("Starting command %s", $cmd));

        $process = new Process($cmd);

        $process->run(function($type, $data) use ($output) {
            $output->write($data, false, OutputInterface::OUTPUT_PLAIN);
        });
    }
}