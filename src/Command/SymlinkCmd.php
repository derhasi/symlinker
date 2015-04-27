<?php
/**
 * @file
 * Symlink.php
 */

namespace derhasi\symlinker\Command;

use derhasi\symlinker\Exception\TargetAlreadyExistsException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedToSourceException;
use derhasi\symlinker\Symlinker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class SymlinkCmd extends Command {

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

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
          ->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Force symlink creation'
          )
          ->addOption(
            'no-backup',
            null,
            InputOption::VALUE_NONE,
            'Do not create a backup of the target, if it already exists'
          )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $file = $input->getOption('file');
        if (!file_exists($file)) {
            throw new FileNotFoundException(null, 0, null, $file);
        }

        $backup = !$input->getOption('no-backup');
        $force = $input->getOption('force');

        $yaml = Yaml::parse($file);

        // Files in the yaml file are relative to the yaml file. Therefore we
        // need to navigate there first.
        chdir(dirname($file));

        $command = $this->getApplication()->find('symlink-single');

        foreach ($yaml as $target => $source)
        {
            $arguments = array(
              'command' => 'symlink-single',
              'target' => $target,
              'source' => $source,
              '--force' => $force,
              '--no-backup' => !$backup,
            );

            $cmd_input = new ArrayInput($arguments);
            $command->run($cmd_input, $output);
        }
    }
}