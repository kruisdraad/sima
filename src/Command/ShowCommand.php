<?php

namespace Sima\Console\Command;

use Sima\Console\Models\File as SimaFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('show');
        $this->setDescription('Shows information based on hash or filename');

        $this->addArgument('hash', inputArgument::REQUIRED, 'specifies the hash of the file you want to show');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $SimaFile = SimaFile::where('hash', '=', $input->getArgument('hash'));

        /*
         * Output filtered table
         */
        if ($SimaFile->count() === 0) {
            echo 'Search did not yield any results, try with search if you could find it'.PHP_EOL;

            return false;
        } else {
            $SimaFile = $SimaFile->get()[0];

            $table = new Table($output);
            $table->setHeaders(['Key', 'Value']);

            $fields = [
        'Hash'                    => 'hash',
                'Filename'        => 'name',
                'Filesize'        => 'size',
                'Extension'       => 'extension',
                'MIME Extension'  => 'mime_extension',
                'MIME Detected'   => 'mime_detected',
                'First seen'      => 'first_seen',
                'Last seen'       => 'last_seen',
                'Count'           => 'count',
                'Scan Time'       => 'scan_time',
                'Detection Rate'  => 'detection_rate',
                'Scanner Results' => 'scan_results',
                'Policies'        => 'TODO',
        'whitelisted'             => 'whitelisted',
                'blacklisted'     => 'blacklisted',
        ];
            $rows = [];

            foreach ($fields as $friendlyName => $name) {
                if ($name === 'name') {
                    $rows[] = [$friendlyName, implode(',', array_keys(json_decode($SimaFile->name, true)))];
                } elseif ($name === 'scan_results') {
                    $scanResults = '';

                    $avData = json_decode($SimaFile->scan_results, true);

                    if (is_array($avData)) {
                        $rows[] = [$friendlyName, implode(PHP_EOL, array_keys($avData)), implode(PHP_EOL, array_values($avData))];
                    }
                } else {
                    $rows[] = [$friendlyName, $SimaFile->$name];
                }
            }

            $table->setRows($rows);
            $table->render();
        }
    }
}
