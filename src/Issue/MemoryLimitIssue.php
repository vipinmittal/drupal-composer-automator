<?php

namespace YourNamespace\DrupalComposerAutomator\Issue;

use YourNamespace\DrupalComposerAutomator\Util\MemoryManager;

class MemoryLimitIssue extends AbstractIssue
{
    private $memoryManager;
    private $requiredMemory = '1G';
    
    public function __construct($composer, $io, $config)
    {
        parent::__construct($composer, $io, $config);
        $this->memoryManager = new MemoryManager();
        
        // Get required memory from config if available
        $configMemory = $this->config->get('memory.min_limit');
        if ($configMemory) {
            $this->requiredMemory = $configMemory;
        }
    }
    
    public function isApplicable(): bool
    {
        return true; // Memory issues can affect any project
    }
    
    public function diagnose(): bool
    {
        $currentLimit = ini_get('memory_limit');
        $currentBytes = $this->memoryManager->memoryToBytes($currentLimit);
        $requiredBytes = $this->memoryManager->memoryToBytes($this->requiredMemory);
        
        $this->hasIssue = ($currentBytes < $requiredBytes);
        
        if ($this->hasIssue) {
            $this->io->write(sprintf(
                '<comment>Memory limit is currently %s, which is less than the recommended %s for Drupal projects.</comment>',
                $currentLimit,
                $this->requiredMemory
            ));
        }
        
        return $this->hasIssue;
    }
    
    public function fix(): bool
    {
        if (!$this->hasIssue) {
            return false;
        }
        
        $result = $this->memoryManager->increaseMemoryLimit($this->requiredMemory);
        
        if ($result) {
            $this->io->write(sprintf(
                '<info>Memory limit increased to %s.</info>',
                ini_get('memory_limit')
            ));
        } else {
            $this->io->write('<error>Failed to increase memory limit. You may need to modify your php.ini file manually.</error>');
        }
        
        return $result;
    }
}