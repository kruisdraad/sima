<?php

namespace Sima\Console\Command;

use Sima\Console\Log;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    public $logger;

    public $debugMode = false;

    public $config;

    public function __construct()
    {
        parent::__construct();

        $this->logger = new Log();

        global $configuration;
        $this->config = $configuration;
    }

    public function log($msg)
    {
        $this->logger->info(
            substr(strrchr(get_class($this), '\\'), 1)
            .": {$msg}"
        );

        $this->debugLog($msg);
    }

    private function debugLog($msg)
    {
        if ($this->debugMode) {
            echo $msg.PHP_EOL;
        }
    }
}
