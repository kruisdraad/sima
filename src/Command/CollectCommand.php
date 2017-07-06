<?php

namespace Sima\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Virustotal;

class CollectCommand extends Command
{
    private $config;

    protected function configure()
    {
        $this->setName('collect');
        $this->setDescription('Collects external information such as AV-Total information for all hashes');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $configuration;
        $this->configuration = $configuration;

        $report = $this->getVirustotalReport($file->hash);

            if ($report) {
echo "{$report['positives']} / {$report['total']}" . PHP_EOL;

                $summary = '';

                foreach($report['scans'] as $scanner => $scanData) {
                    if ($scanData['detected']) {
                        $summary .= "{$scanner}({$scanData['result']}) ";
                    }
                }
echo $summary . PHP_EOL;
	}
    }

    private function getVirustotalReport($resource)
    {
	//$resource = 'a771e484736b4ee8f478dfaa3d5194c10b9f983db86e02601d09a4e8c721a1e0';
	$apiKey = '';
        $file = new \VirusTotal\File($apiKey);
	$response = $file->getReport($resource);

	if ($response['response_code'] == '0') {
	    return false;

	} elseif ($response['response_code'] == '1') {
            return $response;

        } else {
            echo('unknown repsonse');
        }

	die();
    }

}
