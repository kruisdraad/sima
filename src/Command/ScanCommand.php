<?php

namespace Sima\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Sima\Console\Models\File as SimaFile;

class ScanCommand extends Command
{
    private $configuration;

    protected function configure()
    {
        $this->setName('scan');
        $this->setDescription('Scans a file or directory and collects hashes');

        $this->addArgument('path', inputArgument::REQUIRED, 'specifies the path to scan for files placed by Amavis' );
	$this->addArgument('extensions', inputArgument::IS_ARRAY | inputArgument::REQUIRED, 'filtered extensions' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $configuration;
        $this->configuration = $configuration;

        $path = $input->getArgument('path');
        
        $extensions = $this->getFilesFilter($input->getArgument('extensions'));

        $this->startScan($path, $extensions);
    }

    private function startScan($path, $filter)
    {
        $files = $this->getFiles($path, $filter);

	foreach($files as $file) {
	    $SimaFile = SimaFile::where('hash', '=', $file->hash, 'AND')->get();

            if ($SimaFile->count() === 0) {

                $SimaFile = new SimaFile();

                $SimaFile->name            = $file->file;
                $SimaFile->extension       = $file->extension;
                $SimaFile->hash            = $file->hash;
                $SimaFile->mime_extension  = $file->mime;
                $SimaFile->mime_detected   = $file->realmime;
                $SimaFile->size            = $file->size;

                $SimaFile->save();

            } elseif ($SimaFile->count() === 1) {

                $SimaFile = $SimaFile[0];

                $SimaFile->count++;

                $SimaFile->save();

            } else {

                die("Fatal error, database holds multiple of the same hashes");

            }

        }
    }

    private function getFilesFilter($extensions)
    {
	$regex = '';
	
	foreach($extensions as $extension) {
            
            $regex .= preg_quote($extension) . "|";
        }

	$filter = '/(' . substr($regex, 0, -1) . ')$/';

        return $filter;
    }

    private function getFiles($path, $extensions)
    {
	$selection  = [];
        $finder = new Finder();

        $files  = $finder->files()->depth(' < 10')->name($extensions)->in($path);

	foreach ($finder as $file) {
            $fileInfo = new File($file->getRealPath());

	    $selection[] = (object) [
		'path' 		=> $file->getRealPath(),
                'file' 		=> basename($file->getRealPath()),
		'extension' 	=> $fileInfo->getExtension(),
                'mime' 		=> $fileInfo->guessExtension(),
                'realmime' 	=> $fileInfo->getMimeType(),
                'size' 		=> filesize($file->getRealPath()),
		'hash' 		=> hash_file('SHA256', $file->getRealPath()),
	    ];
	}

	return $selection;
    }

}
