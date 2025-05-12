<?php

namespace YourNamespace\DrupalComposerAutomator\Issue;

use YourNamespace\DrupalComposerAutomator\Util\VersionConstraintRelaxer;

class VersionConstraintIssue extends AbstractIssue
{
    private $relaxer;
    private $relaxDrupalCore = true;
    
    public function __construct($composer, $io, $config)
    {
        parent::__construct($composer, $io, $config);
        $this->relaxer = new VersionConstraintRelaxer($composer);
        
        // Get config if available
        $configRelax = $this->config->get('version_constraints.relax_drupal_core');
        if ($configRelax !== null) {
            $this->relaxDrupalCore = (bool) $configRelax;
        }
    }
    
    public function isApplicable(): bool
    {
        return $this->isDrupalProject();
    }
    
    public function diagnose(): bool
    {
        if (!$this->relaxDrupalCore) {
            return false;
        }
        
        $this->hasIssue = $this->relaxer->hasDrupalVersionConstraintIssues();
        
        if ($this->hasIssue) {
            $this->io->write('<comment>Detected modules with strict Drupal core version constraints that may cause compatibility issues.</comment>');
        }
        
        return $this->hasIssue;
    }
    
    public function fix(): bool
    {
        if (!$this->hasIssue) {
            return false;
        }
        
        $result = $this->relaxer->relaxDrupalDependencies();
        
        if ($result) {
            $this->io->write('<info>Relaxed version constraints for Drupal dependencies.</info>');
        } else {
            $this->io->write('<error>Failed to relax version constraints.</error>');
        }
        
        return $result;
    }
}