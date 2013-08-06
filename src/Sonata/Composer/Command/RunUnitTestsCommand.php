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

class RunUnitTestsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('tests:unit')
            ->setDescription('Run tests on each dependency')
            ->addArgument('folder', InputArgument::REQUIRED, 'the folder where the repository is located')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop if a test fail')

            ->addOption('build-folder', null, InputOption::VALUE_REQUIRED, 'The build folder where reports will be generated')
            ->addOption('junit', null, InputOption::VALUE_NONE, 'Log test execution in JUnit XML format to file')
            ->addOption('clover', null, InputOption::VALUE_NONE, 'Generate code coverage report in Clover XML format')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('build-folder') && ($input->getOption('junit') || $input->getOption('clover'))) {
            throw new \RuntimeException('Please provide a build folder');
        }

        if ($input->getOption('build-folder') && !is_writable($input->getOption('build-folder'))) {
            throw new \RuntimeException(sprintf('The build folder %s is not writable', $input->getOption('build-folder')));
        }

        if (!is_dir($input->getArgument('folder'))) {
            throw new \RuntimeException(sprintf('The folder %s does not exist', $input->getArgument('folder')));
        }

        if (!is_dir($input->getArgument('folder')."/vendor")) {
            throw new \RuntimeException(sprintf('The folder vendor does not exist, run the download:dependencies first', $input->getArgument('folder')));
        }

        $projectFolder = realpath($input->getArgument('folder'));
        $vendorFolder  = $projectFolder."/vendor";

        $fs = new Filesystem();

        $errors = array();

        foreach ($this->findTests("phpunit.xml.dist", $input->getArgument('folder')) as $file) {

            $composerFile = $projectFolder."/".$file->getRelativePath()."/composer.json";

            if (is_file($composerFile)) {
                $metadata = @json_decode(file_get_contents($composerFile), true);
                if (!$metadata) {
                    throw new \RuntimeException(sprintf('Unable to parse the composer.json file: %s', $composerFile));
                }
            } else {
                $metadata = array(
                    'name' => 'unknown/unknown'
                );
            }

            $buildFolder = sprintf("%s/%s", $input->getOption('build-folder'), $metadata['name']);

            if ($input->getOption('build-folder')) {
                @mkdir($buildFolder, 0755, true);
            }

            $output->writeln(sprintf("Found <info>%s</info> for package <info>%s</info>", $file, $metadata['name']));

            $tmpVendorFolder = $projectFolder."/".$file->getRelativePath() . "/vendor";

            if (is_dir($tmpVendorFolder)) {
                $output->writeln("<error>The package already have a vendor folder</error>");
                continue;
            }

            $fs->symlink($vendorFolder, $tmpVendorFolder);
            $success = $this->runPHPunit($projectFolder."/".$file->getRelativePath(), $input, $output, $buildFolder);
            $fs->remove($tmpVendorFolder);

            if (!$success) {
                if ($input->getOption('stop-on-error')) {
                    throw new \RuntimeException(sprintf("Tests fail for the following package: %s", $metadata['name']));
                }

                $errors[] = $metadata['name'];
            }
            $output->writeln("");
        }

        if (count($errors)) {
            $output->writeln("Tests fail for the following packages:");

            foreach ($errors as $package) {
                $output->writeln(sprintf(" > %s", $package));
            }

            return 1;
        }

        return 0;
    }

    /**
     * @param string $pattern
     * @param string $directory
     *
     * @return Finder
     */
    protected function findTests($pattern, $directory)
    {
        return Finder::create()
            ->in($directory)
            ->ignoreVCS(true)
            ->path($pattern)
        ;
    }

    /**
     * @param string          $folder
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $buildFolder
     *
     * @return bool
     */
    protected function runPHPunit($folder, InputInterface $input, OutputInterface $output, $buildFolder)
    {
        $output->writeln(sprintf(" >> Running PHPUnit at <info>%s</info>", $folder));

        $cliOptions = array();
        if ($input->getOption('junit')) {
            $cliOptions[] = sprintf('--log-junit %s/junit.xml', $buildFolder);
        }

        if ($input->getOption('clover')) {
            $cliOptions[] = sprintf('--coverage-clover %s/clover.xml', $buildFolder);
        }

        $cmd = sprintf("cd %s && phpunit %s", $folder, implode(" ", $cliOptions));
        $output->writeln(sprintf(" >> PHPUnit command %s", $cmd));

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