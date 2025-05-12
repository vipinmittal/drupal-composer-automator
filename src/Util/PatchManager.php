<?php

namespace YourNamespace\DrupalComposerAutomator\Util;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;

class PatchManager
{
    private $composer;
    private $io;
    private $config;
    
    public function __construct(Composer $composer, IOInterface $io, ConfigManager $config)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $config;
    }
    
    public function validatePatches(): array
    {
        $issues = [];
        $extra = $this->composer->getPackage()->getExtra();
        
        if (!isset($extra['patches']) && !isset($extra['patches-file'])) {
            return $issues;
        }
        
        // Check patches-file if specified
        if (isset($extra['patches-file'])) {
            $patchesFile = $extra['patches-file'];
            if (!file_exists($patchesFile)) {
                $issues[] = ["type" => "missing_file", "file" => $patchesFile];
            } else {
                $patchData = json_decode(file_get_contents($patchesFile), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $issues[] = ["type