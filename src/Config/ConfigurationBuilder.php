<?php

namespace Sima\Console\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigurationBuilder
{
    public $config;

    public function __construct()
    {
        $this->config = $this->make();
    }

    public function make()
    {
        $files = [
        'database.yml',
            'avtotal.yml',
            'mail.yml',
            'base.yml',
    ];

        $configs = $this->getConfigFromFiles($files);

        $processor = new Processor();
        $definition = new ConfigurationDefinitions();

        try {
            $processedConfiguration = $processor->processConfiguration(
            $definition,
            $configs
        );
        } catch (\Exception $e) {
            die(error_log($e->getMessage()));
        }

        return $processedConfiguration;
    }

    public function getConfigFromFiles($files)
    {
        $configs = [];

        foreach ($files as $file) {
            $configs[] = $this->getConfigFromFile($file);
        }

        return $configs;
    }

    public function getConfigFromFile($file)
    {
        $config = null;

        $file = __DIR__.'/../../config/'.$file;

        if (is_file($file)) {
            $config = Yaml::parse(
                file_get_contents($file)
            );
        } else {
            die("Config file {$file} is missing".PHP_EOL);
        }

        return $config;
    }
}
