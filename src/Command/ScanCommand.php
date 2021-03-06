<?php

namespace Sima\Console\Command;

use Sima\Console\Models\File as SimaFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ScanCommand extends AbstractCommand
{
    private $filterMode = false;

    protected function configure()
    {
        $this->setName('scan');
        $this->setDescription('Scans a file or directory and collects hashes');

        $this->addArgument('path', inputArgument::REQUIRED, 'Specifies the path to scan for files placed by Amavis');

        $this->addOption('debug', 'd', inputOption::VALUE_NONE, 'Enables debug mode');
        $this->addOption('filter', 'f', inputOption::VALUE_NONE, 'Enables filter mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('debug')) {
            $this->debugMode = true;
        }

        if ($input->getOption('filter')) {
            $this->log('* Scan starting WITH FILTER POLICY ENABLED');
        } else {
            $this->log('* Scan starting');
        }

        $path = $input->getArgument('path');
        $this->log("* Path {$path} has been selected for scanning");

        $this->log('* Building file list filter based on '.implode(',', $this->config['scan']['extensions']));
        $extensions = $this->getFilesFilter($this->config['scan']['extensions']);

        $this->startScan($path, $extensions);

        $this->log('* Scan complete');
    }

    private function startScan($path, $filter)
    {
        $curDate = date('Y-m-d H:i:s');

        $files = $this->getFiles($path, $filter);

        foreach ($files as $file) {
            $this->log("* File {$file->file} has been selected for scanning");

            $SimaFile = SimaFile::where('hash', '=', $file->hash, 'AND')->get();

            if ($SimaFile->count() === 0) {
                $this->log("- File hash {$file->hash} is new, adding profile");

                $SimaFile = new SimaFile();

                $SimaFile->name = json_encode([$file->file => true]);
                $SimaFile->extension = $file->extension;
                $SimaFile->hash = $file->hash;
                $SimaFile->mime_extension = $file->mime;
                $SimaFile->mime_detected = $file->realmime;
                $SimaFile->size = $file->size;
                $SimaFile->first_seen = $curDate;
                $SimaFile->last_seen = $curDate;

                $SimaFile->save();
            } elseif ($SimaFile->count() === 1) {
                $this->log("- File hash {$file->hash} is exists, adding to counter");

                $SimaFile = $SimaFile[0];

                $knownFiles = json_decode($SimaFile->name, true);
                if (!is_array($knownFiles)) {
                    $knownFiles = [];
                }
                $newFiles = [$file->file => true];

                $SimaFile->name = json_encode(array_merge($knownFiles, $newFiles));

                $SimaFile->last_seen = $curDate;
                $SimaFile->count++;

                $SimaFile->save();
            } else {
                die('Fatal error, database holds multiple of the same hashes');
            }
        }
    }

    private function getFilesFilter($extensions)
    {
        $regex = '';

        foreach ($extensions as $extension) {
            if ($extension === 'ALLFILES') {
                return '/(.*)/';
            }

            $regex .= '.'.preg_quote($extension).'|';
        }

        $filter = '/('.substr($regex, 0, -1).')$/';

        return $filter;
    }

    private function getFiles($path, $extensions)
    {
        $selection = [];
        $finder = new Finder();

        $sort = function (\SplFileInfo $a, \SplFileInfo $b) {
            return strcmp($a->getRealPath(), $b->getRealPath());
        };
        $finder->sort($sort);

        $files = $finder->files()->depth(' < 10')->name($extensions)->in($path);

        foreach ($finder as $file) {
            $fileInfo = new File($file->getRealPath());

            $selection[] = (object) [
        'path'                => $file->getRealPath(),
                'file'        => basename($file->getRealPath()),
        'extension'           => $fileInfo->getExtension(),
                'mime'        => $fileInfo->guessExtension(),
                'realmime'    => $fileInfo->getMimeType(),
                'size'        => filesize($file->getRealPath()),
        'hash'                => hash_file('SHA256', $file->getRealPath()),
        ];
        }

        return $selection;
    }
}
