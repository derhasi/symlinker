<?php
/**
 * @file
 * Symlink.php
 */

namespace derhasi\symlinker\Command\Symlink;

use derhasi\symlinker\Symlinker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class Symlink extends Command {

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('symlink')
          ->setDescription('Ensure symlinks')
          ->addOption(
            'file',
            null,
            InputOption::VALUE_REQUIRED,
            'Location of the YAML file holding the mapping.',
            'symlinker.yml'
          )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file = $input->getOption('file');
        if (!file_exists($file)) {
            throw new FileNotFoundException(null, 0, null, $file);
        }

        $yaml = Yaml::parse($file);

        // Files in the yaml file are relative to the yaml file. Therefore we
        // need to navigate there.
        chdir(dirname($file));

        foreach ($yaml as $target => $source)
        {
            Symlinker::ensureSymlink($target, $source);
            $output->writeln(sprintf('Symlink created: %s to %s', $source, $target));
        }
    }

}