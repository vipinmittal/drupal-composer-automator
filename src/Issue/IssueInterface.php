<?php

namespace YourNamespace\DrupalComposerAutomator\Issue;

interface IssueInterface
{
    /**
     * Check if this issue handler is applicable in the current context
     */
    public function isApplicable(): bool;
    
    /**
     * Diagnose if the issue exists
     */
    public function diagnose(): bool;
    
    /**
     * Check if the issue can be fixed automatically
     */
    public function canFix(): bool;
    
    /**
     * Fix the issue
     */
    public function fix(): bool;
    
    /**
     * Check if the issue needs post-command fixing
     */
    public function needsPostCommandFix(): bool;
    
    /**
     * Apply post-command fixes
     */
    public function postFix(): bool;
}