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

class InstallBehatCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('tests:behat-setup')
            ->setDescription('Install behat requirements into the provided folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'the folder where the repository is located')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the target file if the file already exists')
            ->addOption('uninstall', null, InputOption::VALUE_NONE, 'Delete Behat files')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir($input->getArgument('folder'))) {
            throw new \RuntimeException(sprintf('The folder %s does not exist', $input->getArgument('folder')));
        }

        $files = array(
            'behat.phar'          => 'http://behat.org/downloads/behat.phar',
            'mink.phar'           => 'http://behat.org/downloads/mink.phar',
            'mink_extension.phar' => 'http://behat.org/downloads/mink_extension.phar',
        );

        foreach ($files as $name => $source) {
            $filename = sprintf("%s/%s", $input->getArgument('folder'), $name);
            if (is_file($filename)) {
                if ($input->getOption('delete') || $input->getOption('uninstall')) {
                    $output->writeln(sprintf('Deleting <info>%s</info>', $filename));
                    unlink($filename);
                } else {
                    $output->writeln(sprintf("The file %s already exists", $filename));

                    continue;
                }
            }

            if ($input->getOption('uninstall')) {
                continue;
            }

            $output->write(sprintf("Downloading <info>%s</info> => <info>%s</info> ... ", $source, $filename));

            file_put_contents($filename, file_get_contents($source));

            $output->writeln("<info>OK!</info>");
        }
    }
}