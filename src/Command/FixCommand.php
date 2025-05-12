<?php

namespace YourNamespace\DrupalComposerAutomator\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YourNamespace\DrupalComposerAutomator\Issue\MemoryLimitIssue;
use YourNamespace\DrupalComposerAutomator\Issue\VersionConstraintIssue;
use YourNamespace\DrupalComposerAutomator\Issue\PatchIssue;
use YourNamespace\DrupalComposerAutomator\Issue\TimeoutIssue;
use YourNamespace\DrupalComposerAutomator\Issue\PluginAuthIssue;
use YourNamespace\DrupalComposerAutomator\Util\ConfigManager;

class FixCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('drupal-automator:fix')
            ->setDescription('Fix common Drupal Composer issues')
            ->setHelp(<<<EOT
The <info>drupal-automator:fix</info> command fixes common Drupal Composer issues:

  <info>php composer.phar drupal-automator:fix</info>

This will detect and fix issues like memory limits, version constraints, etc.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->getComposer();
        $io = $this->getIO();
        
        $output->writeln('<info>Drupal Composer Automator: Fixing common issues...</info>');
        
        $config = new ConfigManager($composer, $io);
        
        // Create and run all issue handlers
        $issues = [
            new MemoryLimitIssue($composer, $io, $config),
            new VersionConstraintIssue($composer, $io, $config),
            new PatchIssue($composer, $io, $config),
            new TimeoutIssue($composer, $io, $config),
            new PluginAuthIssue($composer, $io, $config),
        ];
        
        $fixedCount = 0;
        
        foreach ($issues as $issue) {
            if ($issue->isApplicable()) {
                $output->writeln(sprintf('  - Checking %s', get_class($issue)));
                
                if ($issue->diagnose() && $issue->canFix()) {
                    $output->writeln('    <comment>Issue detected, fixing...</comment>');
                    if ($issue->fix()) {
                        $output->writeln('    <info>Fixed successfully!</info>');
                        $fixedCount++;
                    } else {
                        $output->writeln('    <error>Failed to fix issue.</error>');
                    }
                } else {
                    $output->writeln('    <info>No issues detected.</info>');
                }
            }
        }
        
        if ($fixedCount > 0) {
            $output->writeln(sprintf('<info>Fixed %d issues.</info>', $fixedCount));
        } else {
            $output->writeln('<info>No issues needed fixing.</info>');
        }
        
        return 0;
    }
}