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
use Sonata\Composer\Exception\SuccessException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Sonata\Composer\Reporter\Message;

class PackageCommand extends Command
{
    protected $log;

    protected $project;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('package')
            ->setDescription('Create archives from a git repository')
            ->addArgument('project', InputArgument::REQUIRED, 'the project name (used to create the archive)')
            ->addArgument('repository', InputArgument::REQUIRED, 'The GIT url of the repository')
            ->addArgument('destination', InputArgument::REQUIRED, 'The destination folder, must be empty')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'The branch to checkout', 'master')

            ->addOption('build-folder', null, InputOption::VALUE_REQUIRED, 'The build folder where all files will generated (logs, junit, packages, etc ...)')

            ->addOption('reuse', null, InputOption::VALUE_NONE, 'Reuse downloaded file (ie, recreate only package)')
            ->addOption('vcs', null, InputOption::VALUE_NONE, 'include VCS files')
            ->addOption('only-vcs', null, InputOption::VALUE_NONE, 'include VCS files only')
            ->addOption('format', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'archive format', array('zip', 'gz'))

            ->addOption('report-tests', null, InputOption::VALUE_NONE, 'Enable reports for tests')

            ->addOption('run-unit-tests', null, InputOption::VALUE_NONE, 'Run unit tests on each dependency')
            ->addOption('unit-tests-white-list-package', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Package to run tests against')


            ->addOption('run-behat-tests', null, InputOption::VALUE_NONE, 'Run Behat tests')
            ->addOption('ignore-fail-test', null, InputOption::VALUE_NONE, 'Silently fail test (do not stop the package)')

            ->addOption('run-api-generation', null, InputOption::VALUE_NONE, 'Run API Documentation generator')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!preg_match("([a-zA-Z0-1]*)", $input->getArgument('project'))) {
            throw new \RuntimeException('Invalid project name');
        }

        // Creating custom output to log information
        $date = new \DateTime();

        $baseDestination = sprintf("%s", $input->getArgument('destination'));
        $repoDestination = sprintf("%s/repository", $baseDestination);

        if (!$input->getOption('build-folder')) {
            $buildRepository = sprintf("%s/build/%s", $baseDestination, $date->format('Ymd_Gis'));
        } else {
            $buildRepository = $input->getOption('build-folder');
        }

        mkdir($buildRepository, 755, true);

        $this->log = sprintf("%s/console.log", $buildRepository, $date->format("Ymd_Gi"));
        $this->project = $input->getArgument('project');

        $formatter = new DateOutputFormatter($output->getFormatter());

        $output = new ProxyLogger(array(
            $output,
            new StreamOutput(fopen($this->log, 'w'))
        ));
        $output->setFormatter($formatter);

        $output->writeln(sprintf("Starting command with options: %s", $input));

        $output->writeln(array(
            sprintf(" > Base Destination %s", $baseDestination),
            ""
        ));

        if (!$input->getOption('reuse') || !is_dir($repoDestination)) {

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
                    'mode'   => 'install',
                    '--prefer-source' => true
                ), $output)
            ;
        }

        $defaultTestOptions = array(
            'folder' => $repoDestination,
        );

        if ($input->getOption('run-unit-tests')) {

            @mkdir($buildRepository . '/packages');

            $unitTestOptions = array_merge($defaultTestOptions, array(
                'folder'               => $repoDestination,
                '--build-folder'       => $buildRepository . '/packages',
                '--white-list-package' => $input->getOption('unit-tests-white-list-package')
            ));

            if ($input->getOption('report-tests')) {
                $unitTestOptions = array_merge($unitTestOptions, array(
                    '--junit'  => true,
                    '--clover' => true,
                ));
            }

            try {
                $this->runCommand('tests:unit', $unitTestOptions, $output);
            } catch(SuccessException $e) {
                if (!$input->getOption('ignore-fail-test')) {
                    throw $e;
                }
            }
        }

        if ($input->getOption('run-behat-tests')) {
            $behatTestOptions = array_merge($defaultTestOptions, array(
                '--build-folder' => $buildRepository
            ));

            if ($input->getOption('report-tests')) {
                $behatTestOptions = array_merge($behatTestOptions, array('--format' => 'junit'));
            }

            try {
                $this->runCommand('tests:behat-setup', array(
                    'folder'   => $repoDestination,
                    '--delete' => true
                ), $output);

                $this->runCommand('tests:behat', $behatTestOptions, $output);

                $this->runCommand('tests:behat-setup', array(
                    'folder'      => $repoDestination,
                    '--uninstall' => true
                ), $output);
            } catch(SuccessException $e) {
                if (!$input->getOption('ignore-fail-test')) {
                    throw $e;
                }
            }
        }

        if ($input->getOption('report-tests')) {
            $this->runCommand('tests:merge-junit', array(
                'folder' => $buildRepository,
                'target' => $buildRepository."/junit.xml"
            ), $output);
        }

        // delete cache/* folder
        $output->writeln(sprintf("Deleting cache folder: %s/cache/*", $repoDestination));
        $p = new Process(sprintf("rm -rf %s/app/cache/*", $repoDestination));
        $p->run();

        if (in_array('zip', $input->getOption('format'))) {
            if ($input->getOption('vcs') || $input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder'      => $repoDestination,
                    'destination' => sprintf("%s/%s_vcs.zip", $buildRepository, $input->getArgument('project')),
                    '--vcs'       => 'true'
                ), $output);
            }

            if (!$input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder'      => $repoDestination,
                    'destination' => sprintf("%s/%s.zip", $buildRepository, $input->getArgument('project')),
                ), $output);
            }
        }

        if (in_array('gz', $input->getOption('format'))) {
            if ($input->getOption('vcs') || $input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s_vcs.tar.gz", $buildRepository, $input->getArgument('project')),
                    '--vcs' => 'true'
                ), $output);
            }

            if (!$input->getOption('only-vcs')) {
                $this->runCommand('archive:create', array(
                    'folder' => $repoDestination,
                    'destination' => sprintf("%s/%s.tar.gz", $buildRepository, $input->getArgument('project')),
                ), $output);
            }
        }

        if ($input->getOption('run-api-generation')) {
            @mkdir( $buildRepository . '/api');
            $this->runCommand('api:generate', array(
                'folder' => $repoDestination,
                'build-folder' => $buildRepository.'/api',
            ), $output);
        }

        $output->writeln('<info>Done!</info>');

        $this->getApplication()->sendReport(Message::create($this->project, file_get_contents($this->log), 'SUCCESS'));
    }

    /**
     * @param string          $command
     * @param array           $args
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

        $output->writeln(sprintf("Starting command: <info>%s</info>", $input->__toString()));

        $exception = $success = false;
        try {
            $success = $command->run($input, $output) === 0;
        } catch (\Exception $e) {
            $success = false;
            $exception = $e;
        }

        if (!$success) {
            $this->getApplication()->sendReport(Message::create($this->project, file_get_contents($this->log), 'ERROR'));
        }

        if (!$success) {
            throw new SuccessException(sprintf('<error>The command %s failed</error>', $command->getName()));
        }

        if ($exception) {
            throw $exception;
        }

        $output->writeln("end command");

        return $this;
    }
}