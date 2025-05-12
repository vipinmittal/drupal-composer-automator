<?php

namespace YourNamespace\DrupalComposerAutomator\Util;

class MemoryManager
{
    public function increaseMemoryLimit($minMemory = '1G')
    {
        $currentLimit = ini_get('memory_limit');
        $currentBytes = $this->memoryToBytes($currentLimit);
        $requiredBytes = $this->memoryToBytes($minMemory);

        if ($currentBytes < $requiredBytes) {
            return ini_set('memory_limit', $minMemory) !== false;
        }

        return false;
    }

    public function memoryToBytes($memoryString)
    {
        $memoryString = trim($memoryString);
        $unit = strtolower(substr($memoryString, -1));
        $value = (int) $memoryString;

        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return $value;
    }
}