<?php

namespace Sima\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarkCommand extends Command
{
    private $config;

    protected function configure()
    {
        $this->setName('mark');
        $this->setDescription('Marks an file hash as good (whitelist) or bad (blacklist)');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $configuration;
        $this->configuration = $configuration;

    }

}
