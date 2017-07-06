<?php

namespace Sima\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Sima\Console\Models\File as SimaFile;

class ShowCommand extends Command
{
    private $config;

    protected function configure()
    {
        $this->setName('show');
        $this->setDescription('Shows information based on hash or filename');

        $this->addArgument('hash', inputArgument::REQUIRED, 'specifies the hash of the file you want to show' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $configuration;
        $this->configuration = $configuration;

        $SimaFile = SimaFile::where('hash', '=', $input->getArgument('hash'));

        /*
         * Output filtered table
         */
        if ($SimaFile->count() === 0) {
            echo "Search did not yield any results, try with search if you could find it" . PHP_EOL;

            return false;
        } else {
            $SimaFile = $SimaFile->get()[0];

            $table = new Table($output);
            $table->setHeaders(['Key', 'Value',]);

            $fields = [
		'Hash' 		=> 'hash',
                'Filename'	=> 'name',
                'Filesize'      => 'size',
                'Extension'     => 'extension',
                'MIME Extension'=> 'mime_extension',
                'MIME Detected' => 'mime_detected',
                'First seen'	=> 'first_seen',
                'Last seen'     => 'last_seen',
                'Count'		=> 'count',
                'Policies'	=> 'TODO',
		'whitelisted'   => 'whitelisted',
                'blacklisted'   => 'blacklisted',
	    ];
            $rows = [];

            foreach($fields as $friendlyName => $name) {
                if ($name === 'name') {
                    $rows[] = [ $friendlyName, implode(',', array_keys(json_decode($SimaFile->name, true))) ];
                } else {
                    $rows[] = [ $friendlyName, $SimaFile->$name ];
                }
            }

            $table->setRows($rows);
            $table->render();
        }

    }

}
