<?php

namespace YourNamespace\DrupalComposerAutomator;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use YourNamespace\DrupalComposerAutomator\Command\FixCommand;
use YourNamespace\DrupalComposerAutomator\Command\DiagnoseCommand;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [
            new FixCommand(),
            new DiagnoseCommand(),
        ];
    }
}