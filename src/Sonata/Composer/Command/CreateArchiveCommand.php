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
use Symfony\Component\Process\Process;

class CreateArchiveCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('archive:create')
            ->setDescription('Create an archive from the provided folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'the folder to archive')
            ->addArgument('destination', InputArgument::REQUIRED, 'the archive name and location, compression is guessed from the filename')
            ->addOption('vcs', null, InputOption::VALUE_NONE, 'include VCS files');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $ext = pathinfo($input->getArgument('destination'), PATHINFO_EXTENSION);

        if (!in_array($ext, array('zip', 'gz'))) {
            throw new \RuntimeException(sprintf('Invalid filename: %s', $input->getArgument('destination')));
        }

        $fs = new Filesystem();

        if ($fs->exists($input->getArgument('destination'))) {
            $fs->remove($input->getArgument('destination'));
        }

        $tmpFile = sprintf("%s/composer_archive_%s.%s",
            sys_get_temp_dir(),
            sha1($input->getArgument('destination')),
            $ext
        );

        if ($fs->exists($tmpFile)) {
            $fs->remove($tmpFile);
        }

        if ($ext == 'gz') {
            $cmd = sprintf("cd %s && tar czf %s . %s",
                $input->getArgument('folder'),
                $tmpFile,
                $input->getOption('vcs') ? '' : '--exclude-vcs'
            );
        } elseif ($ext == 'zip') {
            $cmd = sprintf("cd %s && zip -r -q %s . %s",
                $input->getArgument('folder'),
                $tmpFile,
                $input->getOption('vcs') ? '' : '-x *.git* *.svn*'
            );
        }

        $output->writeln(sprintf("Creating temporary file: <info>%s</info>", $tmpFile));
        $output->writeln(sprintf("Starting command %s", $cmd));

        $process = new Process($cmd);
        $process->setTimeout(null);

        $process->run(function($type, $data) use ($output) {
            $output->write($data, false, OutputInterface::OUTPUT_PLAIN);
        });


        $fs->rename($tmpFile, $input->getArgument('destination'));
    }
}