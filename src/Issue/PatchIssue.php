<?php

namespace YourNamespace\DrupalComposerAutomator\Issue;

use YourNamespace\DrupalComposerAutomator\Util\PatchManager;

class PatchIssue extends AbstractIssue
{
    private $patchManager;
    private $issues = [];
    
    public function __construct($composer, $io, $config)
    {
        parent::__construct($composer, $io, $config);
        $this->patchManager = new PatchManager($composer, $io, $config);
    }
    
    public function isApplicable(): bool
    {
        $extra = $this->composer->getPackage()->getExtra();
        return isset($extra['patches']) || isset($extra['patches-file']);
    }
    
    public function diagnose(): bool
    {
        if (!$this->isApplicable()) {
            return false;
        }
        
        $this->issues = $this->patchManager->validatePatches();
        $this->hasIssue = !empty($this->issues);
        
        if ($this->hasIssue) {
            $this->io->write('<comment>Found issues with patch configuration:</comment>');
            foreach ($this->issues as $issue) {
                switch ($issue['type']) {
                    case 'missing_file':
                        $this->io->write(sprintf(
                            '<comment>  - Patches file not found: %s</comment>',
                            $issue['file']
                        ));
                        break;
                        
                    case 'invalid_json':
                        $this->io->write(sprintf(
                            '<comment>  - Invalid JSON in patches file: %s</comment>',
                            $issue['file']
                        ));
                        break;
                        
                    case 'invalid_url':
                        $this->io->write(sprintf(
                            '<comment>  - Invalid patch URL for package %s: %s</comment>',
                            $issue['package'],
                            $issue['url']
                        ));
                        break;
                }
            }
        }
        
        return $this->hasIssue;
    }
    
    public function canFix(): bool
    {
        if (empty($this->issues)) {
            return false;
        }
        
        // We can fix missing files and invalid JSON, but not invalid URLs
        foreach ($this->issues as $issue) {
            if ($issue['type'] === 'invalid_url') {
                return false;
            }
        }
        
        return true;
    }
    
    public function fix(): bool
    {
        if (!$this->hasIssue || !$this->canFix()) {
            return false;
        }
        
        return $this->patchManager->fixPatchIssues($this->issues);
    }
    
    public function needsPostCommandFix(): bool
    {
        return false; // Patch issues are handled during pre-command
    }
    
    public function postFix(): bool
    {
        return true; // No post-command fixes needed
    }
}