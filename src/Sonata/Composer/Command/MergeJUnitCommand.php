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

use Sonata\Composer\JUnit\JUnitMerger;
use Sonata\Composer\JUnit\JUnitWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MergeJUnitCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('tests:merge-junit')
            ->setDescription('Merge JUnit reports into one file')
            ->addArgument('folder', InputArgument::REQUIRED, 'the folder where the repository is located')
            ->addArgument('target', InputArgument::REQUIRED, 'The target file used to store JUnit report')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir($input->getArgument('folder'))) {
            throw new \RuntimeException(sprintf('The folder %s is not writable', $input->getArgument('folder')));
        }

        if (is_file($input->getArgument('target'))) {
            unlink($input->getArgument('target'));
        }

        $f = Finder::create()
            ->ignoreVCS(true)
            ->in($input->getArgument('folder'))
            ->name('*.xml');

        $output->writeln(sprintf("Merging TestSuite from %s", $input->getArgument('folder')));

        $merger = new JUnitMerger();
        $testsuite = $merger->merge($f->getIterator());

        $output->writeln(sprintf("Saving TestSuite to %s", $input->getArgument('target')));

        $writer = new JUnitWriter();
        $writer->save($testsuite, $input->getArgument('target'));

        $output->writeln("Done!");
    }
}