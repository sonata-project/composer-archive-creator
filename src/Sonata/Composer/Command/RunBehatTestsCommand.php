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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class RunBehatTestsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('tests:behat')
            ->setDescription('Start behat test from the provider folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'the folder where the repository is located')
            ->addOption('build-folder', null, InputOption::VALUE_REQUIRED, 'The build folder where report will be generated')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('build-folder') && $input->getOption('format')) {
            throw new \RuntimeException('Please provide a build folder');
        }

        if ($input->getOption('build-folder') && !is_writable($input->getOption('build-folder'))) {
            throw new \RuntimeException(sprintf('The build folder %s is not writable', $input->getOption('build-folder')));
        }

        if (!is_dir($input->getArgument('folder'))) {
            throw new \RuntimeException(sprintf('The folder %s does not exist', $input->getArgument('folder')));
        }

        $output->writeln(sprintf(" >> Running Behat at <info>%s</info>", $input->getArgument('folder')));

        $formats = array(
            'pretty' => '.log',
            'progress' => '.log',
            'html' => '.html',
            'junit' => '',
            'failed' => '.log',
            'snippets' => '.log'
        );

        if ($input->getOption('format') && !array_key_exists($input->getOption('format'), $formats)) {
            throw new \RuntimeException(sprintf('Invalid format', $input->getOption('format')));
        }

        $cliOptions = array();
        if ($input->getOption('format')) {
            $cliOptions[] = sprintf('--format %s --out %s/behat%s',
                $input->getOption('format'),
                $input->getOption('build-folder'),
                $formats[$input->getOption('format')]
            );
        }

        $cmd = sprintf("cd %s && php behat.phar %s",
            $input->getArgument('folder'),
            implode(" ", $cliOptions)
        );

        $output->writeln(sprintf("Starting command: %s", $cmd));
        $process = new Process($cmd);
        $process->setTimeout(null);

        $out = "";

        // allows to nicely log data ....
        $process->run(function($type, $data) use (&$out) {
            $out .= $data;
        });

        if ($process->getExitCode() !== 0) {
            $output->writeln(explode("\n", $out));

            $output->writeln("");
            $output->writeln(sprintf("<question>Exit code: %s (%s)</question>", $process->getExitCodeText(), $process->getExitCode()));
            $output->writeln("\n");

        } else {
            $output->writeln(" >> Tests OK !");
        }

        return $process->getExitCode() === 0;
    }
}