<?php

namespace YourNamespace\DrupalComposerAutomator;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Plugin\Capable;
use YourNamespace\DrupalComposerAutomator\Util\ConfigManager;
use YourNamespace\DrupalComposerAutomator\Issue\MemoryLimitIssue;
use YourNamespace\DrupalComposerAutomator\Issue\VersionConstraintIssue;
use YourNamespace\DrupalComposerAutomator\Issue\PatchIssue;
use YourNamespace\DrupalComposerAutomator\Issue\TimeoutIssue;
use YourNamespace\DrupalComposerAutomator\Issue\PluginAuthIssue;

class DrupalComposerAutomator implements PluginInterface, EventSubscriberInterface, Capable
{
    protected $composer;
    protected $io;
    protected $config;
    protected $issues = [];

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = new ConfigManager($composer, $io);
        
        // Register all issue handlers
        $this->registerIssues();
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Clean up resources
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Clean up resources
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'beforeCommand',
            ScriptEvents::PRE_UPDATE_CMD => 'beforeCommand',
            ScriptEvents::POST_INSTALL_CMD => 'afterCommand',
            ScriptEvents::POST_UPDATE_CMD => 'afterCommand',
        ];
    }

    public function getCapabilities()
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => 'YourNamespace\DrupalComposerAutomator\CommandProvider',
        ];
    }

    public function beforeCommand()
    {
        $this->io->write('<info>Drupal Composer Automator: Checking for potential issues...</info>');
        
        // Run pre-command checks for all registered issues
        foreach ($this->issues as $issue) {
            if ($issue->isApplicable()) {
                $issue->diagnose();
                if ($issue->canFix() && $this->config->isAutoFixEnabled()) {
                    $issue->fix();
                }
            }
        }
    }

    public function afterCommand()
    {
        $this->io->write('<info>Drupal Composer Automator: Applying post-command fixes...</info>');
        
        // Run post-command fixes for all registered issues
        foreach ($this->issues as $issue) {
            if ($issue->isApplicable() && $issue->needsPostCommandFix()) {
                $issue->postFix();
            }
        }
    }

    protected function registerIssues()
    {
        // Register all issue handlers
        $this->issues[] = new MemoryLimitIssue($this->composer, $this->io, $this->config);
        $this->issues[] = new VersionConstraintIssue($this->composer, $this->io, $this->config);
        $this->issues[] = new PatchIssue($this->composer, $this->io, $this->config);
        $this->issues[] = new TimeoutIssue($this->composer, $this->io, $this->config);
        $this->issues[] = new PluginAuthIssue($this->composer, $this->io, $this->config);
    }
}