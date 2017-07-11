<?php

namespace Sima\Console\Command;

use Sima\Console\Models\File as SimaFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('search');
        $this->setDescription('Lists filesnames and hashes based on search arguments');

        $this->addOption('filename', 'f', inputOption::VALUE_REQUIRED, 'Filename to search for, including extension');
        $this->addOption('extension', 'e', inputOption::VALUE_REQUIRED, 'Extension to search for, with prefix dot (.)');
        $this->addOption('timefirst', 'x', inputOption::VALUE_REQUIRED, 'Time in YYYYMMDD or YYYYMMDD_HHMM when a hash was first seen');
        $this->addOption('timelast', 'y', inputOption::VALUE_REQUIRED, 'Time in YYYYMMDD or YYYYMMDD_HHMM when a hash was last seen');
        $this->addOption('really', 'r', inputOption::VALUE_NONE, 'Used without search options to confirm you really want to see the entire database scrolling on your screen');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (
            empty($input->getOption('filename')) &&
            empty($input->getOption('extension')) &&
            empty($input->getOption('timefirst')) &&
            empty($input->getOption('timelast')) &&
            $input->getOption('really') === false
        ) {
            echo 'Not dumping entire database to console. Try help, adding search options or use --really to see magic on your console'.PHP_EOL;

            return false;
        }

        $SimaFile = SimaFile::where('hash', '!=', null);

        /*
         * Add additional filters
         */
        if ((!empty($input->getOption('timefirst')) or !empty($input->getOption('timelast')))) {
            if ($input->getOption('timefirst') === null or $input->getOption('timelast') === null) {
                die('Error: --timefirst and --timelast must both be used for timefilters'.PHP_EOL);
            }

            //TODO Add time filters
        }

        if (!empty($input->getOption('filename'))) {
            $SimaFile->where('name', 'LIKE', "%{$input->getOption('filename')}%", 'AND');
        }

        if (!empty($input->getOption('extension'))) {
            $SimaFile->where('extension', 'LIKE', "%{$input->getOption('extension')}%", 'AND');
        }

        /*
         * Output filtered table
         */
        if ($SimaFile->count() === 0) {
            echo 'Search did not yield any results, try harder'.PHP_EOL;

            return false;
        } else {
            $table = new Table($output);

            $table->setHeaders(['Hash', 'Filename', 'First seen', 'Count']);

            $rows = [];
            foreach ($SimaFile->get() as $file) {
                $rows[] = [
                    $file->hash,
                    implode(',', array_keys(json_decode($file->name, true))),
                    $file->last_seen,
                    $file->count,
                ];
            }

            $table->setRows($rows);
            $table->render();
        }
    }
}
