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

class DownloadDependenciesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('download:dependencies')
            ->setDescription('Download composer dependencies')
            ->addArgument('folder', InputArgument::REQUIRED, 'the composer file')
            ->addArgument('mode', InputArgument::OPTIONAL, 'the mode to install file', 'update');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('mode') == 'update') {
            $mode = 'update';
        } else {
            $mode = 'install';
        }

        $cmd = sprintf("cd %s && %s %s --prefer-source --no-dev",
            $input->getArgument('folder'),
            $this->getApplication()->getComposerExecutable(),
            $mode
        );

        $output->writeln(array(
            sprintf("Starting command: %s", $cmd),
            " > <info>This can take a while, go take a coffee.</info>",
            " > <info> ... you can either review some documentation about sonata/symfony or review the last tweets</info>"
        ));

        $process = new Process($cmd);
        $process->setTimeout(null);

        $process->run(function($type, $data) use ($output) {
            $output->write($data, false, OutputInterface::OUTPUT_PLAIN);
        });
    }
}