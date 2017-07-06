<?php

namespace Sima\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Sima\Console\Models\File as SimaFile;

class SearchCommand extends Command
{
    private $config;

    protected function configure()
    {
        $this->setName('search');
        $this->setDescription('Lists filesnames and hashes based on search arguments');

        $this->addOption('filename', 'f', inputOption::VALUE_REQUIRED, 'Filename to search for, including extension' );
        $this->addOption('extension', 'e', inputOption::VALUE_REQUIRED, 'Extension to search for, with prefix dot (.)' );
        $this->addOption('timefirst', 'tf', inputOption::VALUE_REQUIRED, 'Time in YYYYMMDD or YYYYMMDD_HHMM when a hash was first seen' );
        $this->addOption('timelast', 'tl', inputOption::VALUE_REQUIRED, 'Time in YYYYMMDD or YYYYMMDD_HHMM when a hash was last seen' );
        $this->addOption('really', 'r', inputOption::VALUE_NONE, 'Used without search options to confirm you really want to see the entire database scrolling on your screen');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $configuration;
        $this->configuration = $configuration;

        if(
            empty($input->getOption('filename')) &&
            empty($input->getOption('extension')) &&
            empty($input->getOption('timefirst')) &&
            empty($input->getOption('timelast')) &&
            $input->getOption('really') === false
        ) {
            echo "Not dumping entire database to console. Try help, adding search options or use --really to see magic on your console". PHP_EOL;
            return false;
        }

        $SimaFile = SimaFile::where('hash', '!=', null);

        //TODO ADD FILTERS

        /*
         * Output filtered table
         */
        if ($SimaFile->count() === 0) {
            echo "Search did not yield any results, try harder" . PHP_EOL;

            return false;
        } else {
            $table = new Table($output);

            $table->setHeaders(['Hash', 'Filename', 'First seen', 'Count']);

            $rows = [];
            foreach($SimaFile->get() as $file) {
                $rows[] = [
                    $file->hash,
                    $file->name,
                    $file->last_seen,
                    $file->count,
                ];
            }

            $table->setRows($rows);
            $table->render();
        }

                   
    }

}
