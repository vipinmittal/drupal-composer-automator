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

class DiagnoseCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('drupal-automator:diagnose')
            ->setDescription('Diagnose common Drupal Composer issues')
            ->setHelp(<<<EOT
The <info>drupal-automator:diagnose</info> command diagnoses common Drupal Composer issues without fixing them:

  <info>php composer.phar drupal-automator:diagnose</info>

This will detect issues like memory limits, version constraints, etc. and report them without making changes.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->getComposer();
        $io = $this->getIO();
        
        $output->writeln('<info>Drupal Composer Automator: Diagnosing common issues...</info>');
        
        $config = new ConfigManager($composer, $io);
        
        // Create and run all issue handlers
        $issues = [
            new MemoryLimitIssue($composer, $io, $config),
            new VersionConstraintIssue($composer, $io, $config),
            new PatchIssue($composer, $io, $config),
            new TimeoutIssue($composer, $io, $config),
            new PluginAuthIssue($composer, $io, $config),
        ];
        
        $issuesFound = 0;
        
        foreach ($issues as $issue) {
            if ($issue->isApplicable()) {
                $output->writeln(sprintf('  - Checking %s', get_class($issue)));
                
                if ($issue->diagnose()) {
                    $output->writeln('    <comment>Issue detected!</comment>');
                    $issuesFound++;
                    
                    if ($issue->canFix()) {
                        $output->writeln('    <info>This issue can be fixed automatically with the fix command.</info>');
                    } else {
                        $output->writeln('    <error>This issue requires manual intervention.</error>');
                    }
                } else {
                    $output->writeln('    <info>No issues detected.</info>');
                }
            }
        }
        
        if ($issuesFound > 0) {
            $output->writeln(sprintf('<comment>Found %d issues that need attention.</comment>', $issuesFound));
            $output->writeln('<info>Run "composer drupal-automator:fix" to attempt automatic fixes.</info>');
        } else {
            $output->writeln('<info>No issues detected. Your Drupal project looks healthy!</info>');
        }
        
        return 0;
    }
}