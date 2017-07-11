<?php

namespace Sima\Console\Command;

use Sima\Console\Models\File as SimaFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Virustotal;

class CollectCommand extends AbstractCommand
{
    private $apiKey;

    protected function configure()
    {
        $this->setName('collect');
        $this->setDescription('Collects external information such as AV-Total information for all hashes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->apiKey = $this->config['avtotal']['apikey'];

        /*
         * We select all files that have not been checked AND everything not older then 48 hours.
         * Anything that was scanned before, but younger then 48 hours will be scanned if the results
         * are older then 1 hour and only if the API has enough request left (rate limited !)
         */

        $SimaFile = SimaFile::where('scan_time', '=', null)
        ->where('scan_time', '<=', date('Y-m-d H:i:s', (time() - 3600)), 'OR');

        if ($SimaFile->count() === 0) {
            echo 'No new hashes to request at Virustotal'.PHP_EOL;

            return false;
        } else {
            foreach ($SimaFile->get() as $file) {
                echo "Requesting data for hash {$file->hash}".PHP_EOL;

                $report = $this->getVirustotalReport($file->hash);

                if ($report) {
                    $file->detection_rate = "{$report['positives']} / {$report['total']}";

                    $summary = [];

                    foreach ($report['scans'] as $scanner => $scanData) {
                        if ($scanData['detected']) {
                            $summary[$scanner] = $scanData['result'];
                        }
                    }
                    $file->scan_results = json_encode($summary);
                }

                $file->scan_time = date('Y-m-d H:i:s');
                $file->save();
            }
        }
    }

    private function getVirustotalReport($resource)
    {
        //uncomment this to make everything look bad
    //$resource = 'a771e484736b4ee8f478dfaa3d5194c10b9f983db86e02601d09a4e8c721a1e0';

    try {
        $api = new \VirusTotal\File($this->apiKey);
        $response = $api->getReport($resource);
    } catch (\Exception $e) {
        die('No more API calls left, try again later'.PHP_EOL);
        //error_log($e->getMessage());
    }

        if ($response['response_code'] == '0') {
            return false;
        } elseif ($response['response_code'] == '1') {
            return $response;
        } else {
            echo 'unknown repsonse';
        }

        die();
    }
}
