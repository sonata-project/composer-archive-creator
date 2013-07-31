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

use Sonata\Composer\Console\DateOutputFormatter;
use Sonata\Composer\Console\ProxyLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class PackageCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('package')
            ->setDescription('Create archives from a git repository')
            ->addArgument('name', InputArgument::REQUIRED, 'the project name (used to create the archive)')
            ->addArgument('repository', InputArgument::REQUIRED, 'The GIT url of the repository')
            ->addArgument('destination', InputArgument::REQUIRED, 'The destination folder, must be empty')
            ->addOption('reuse', null, InputOption::VALUE_NONE, 'Reuse downloaded file (ie, recreate only package)')
            ->addOption('vcs', null, InputOption::VALUE_NONE, 'include VCS files')
            ->addOption('only-vcs', null, InputOption::VALUE_NONE, 'include VCS files only')
            ->addOption('format', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'archive format', array('zip', 'gz'))
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'The branch to checkout', 'master')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!preg_match("([a-zA-Z0-1]*)", $input->getArgument('name'))) {
            throw new \RuntimeException('Invalid project name');
        }

        // Creating custom output to log information
        $date = new \DateTime();

        $log = sprintf("logs/%s_%s.log",$input->getArgument('name'), $date->format("Ymd_Gi"));

        $formatter = new DateOutputFormatter($output->getFormatter());

        $output = new ProxyLogger(array(
            $output,
            new StreamOutput(fopen($log, 'w'))
        ));
        $output->setFormatter($formatter);

        $output->writeln(sprintf("Starting command with options: %s", $input));

        $baseDestination = sprintf("%s", $input->getArgument('destination'));
        $repoDestination = sprintf("%s/repository", $input->getArgument('destination'));

        if (!$input->getOption('reuse')) {

            if (is_dir($repoDestination)) {
                $output->writeln(sprintf("Deleting %s", $repoDestination));

                $process = new Process(sprintf("rm -rf %s", $repoDestination));
                $process->run();
            }

            @mkdir($baseDestination, 0777, true);

            if (!is_writable($baseDestination)) {
                throw new \RuntimeException(sprintf("<error>Unable to create folder %s</error>", $baseDestination));
            }

            $this
                ->runCommand('download:repository', array(
                    'repository'  => $input->getArgument('repository'),
                    'destination' => $repoDestination,
                    '--branch'    => $input->getOption('branch'),
                ), $output)
                ->runCommand('download:dependencies', array(
                    'folder' => $repoDestination,
                    'mode'   => 'install'
                ), $output)
            ;
        }

        if (in_array('zip', $input->getOption('format'))) {
            if ($input->getOption('vcs') || $input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s_vcs.zip", $baseDestination, $input->getArgument('name')),
                    '--vcs' => 'true'
                ), $output);
            }

            if (!$input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s.zip", $baseDestination, $input->getArgument('name')),
                ), $output);
            }
        }

        if (in_array('gz', $input->getOption('format'))) {
            if ($input->getOption('vcs') || $input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s_vcs.tar.gz", $baseDestination, $input->getArgument('name')),
                    '--vcs' => 'true'
                ), $output);
            }

            if (!$input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s.tar.gz", $baseDestination, $input->getArgument('name')),
                ), $output);
            }
        }

        $output->writeln('<info>Done!</info>');
    }


    /**
     * @param $command
     * @param array $args
     * @param OutputInterface $output
     *
     * @return CreateArchiveCommand
     *
     * @throws \RuntimeException
     */
    public function runCommand($command, array $args, OutputInterface $output)
    {
        $input = new ArrayInput(array_merge($args, array(
            'command' => $command
        )));
        $command = $this->getApplication()->find($command);

        $output->writeln(sprintf("Starting command: <info>%s %s</info>", $command->getName(), $input->__toString()));

        if ($command->run($input, $output) !== 0) {
            throw new \RuntimeException(sprintf('<error>The command %s failed</error>', $command->getName()));
        }

        $output->writeln("end command");

        return $this;
    }
}