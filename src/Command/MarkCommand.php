<?php

namespace Sima\Console\Command;

use Sima\Console\Models\File as SimaFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarkCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('mark');
        $this->setDescription('Marks an file hash as good (whitelist) or bad (blacklist)');

        $this->addArgument('hash', inputArgument::REQUIRED, 'specifies the hash of the file you want to change the action on');

        $this->addOption('blacklist', 'b', inputOption::VALUE_NONE, 'Sets blacklist action on the given hash');
        $this->addOption('whitelist', 'w', inputOption::VALUE_NONE, 'Sets whitelist action on the given hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('blacklist') && $input->getOption('whitelist')) {
            echo 'make up your mind'.PHP_EOL;

            return false;
        }

        $SimaFile = SimaFile::where('hash', '=', $input->getArgument('hash'), 'AND');

        if ($SimaFile->count() === 0) {
            echo 'Search did not yield any results, try with search if you could find it'.PHP_EOL;

            return false;
        }

        $SimaFile = $SimaFile->get()[0];

        if ($input->getOption('blacklist')) {
            $SimaFile->blacklisted = true;
            $SimaFile->whitelisted = false;
            echo "Set blacklist option on hash {$input->getArgument('hash')} completed".PHP_EOL;
        }

        if ($input->getOption('whitelist')) {
            $SimaFile->blacklisted = false;
            $SimaFile->whitelisted = true;
            echo "Set whitelist option on hash {$input->getArgument('hash')} completed".PHP_EOL;
        }

        $SimaFile->save();
    }
}
