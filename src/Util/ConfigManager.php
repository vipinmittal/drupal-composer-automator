<?php

namespace YourNamespace\DrupalComposerAutomator\Util;

use Composer\Composer;
use Composer\IO\IOInterface;

class ConfigManager
{
    private $composer;
    private $io;
    private $config = [];
    private $configFile = 'drupal-composer-automator.json';

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->loadConfig();
    }

    public function loadConfig()
    {
        // Default configuration
        $this->config = [
            'autofix' => true,
            'memory' => [
                'min_limit' => '1G',
            ],
            'version_constraints' => [
                'relax_drupal_core' => true,
            ],
            'patches' => [
                'auto_apply' => true,
            ],
        ];
        
        // Try to load configuration from file
        $configPath = getcwd() . '/' . $this->configFile;
        
        if (file_exists($configPath)) {
            $fileConfig = json_decode(file_get_contents($configPath), true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($fileConfig)) {
                $this->config = array_replace_recursive($this->config, $fileConfig);
            } else {
                $this->io->writeError('<warning>Invalid configuration file: ' . $this->configFile . '</warning>');
            }
        }
    }

    public function get($key, $default = null)
    {
        $parts = explode('.', $key);
        $config = $this->config;
        
        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                return $default;
            }
            
            $config = $config[$part];
        }
        
        return $config;
    }

    public function isAutoFixEnabled()
    {
        return (bool) $this->get('autofix', true);
    }
}