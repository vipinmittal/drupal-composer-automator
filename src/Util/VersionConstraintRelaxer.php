<?php

namespace YourNamespace\DrupalComposerAutomator\Util;

use Composer\Package\PackageInterface;
use Composer\Package\Link;
use Composer\Semver\Constraint\Constraint;

class VersionConstraintRelaxer
{
    private $composer;
    private $relaxablePackages = [];

    public function __construct($composer)
    {
        $this->composer = $composer;
    }

    public function hasDrupalVersionConstraintIssues()
    {
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $this->relaxablePackages = [];
        
        foreach ($packages as $package) {
            if ($this->isDrupalPackage($package) && $this->hasStrictConstraints($package)) {
                $this->relaxablePackages[] = $package;
            }
        }
        
        return !empty($this->relaxablePackages);
    }

    public function relaxDrupalDependencies()
    {
        if (empty($this->relaxablePackages)) {
            $this->hasDrupalVersionConstraintIssues();
        }
        
        if (empty($this->relaxablePackages)) {
            return false;
        }
        
        $rootPackage = $this->composer->getPackage();
        $requires = $rootPackage->getRequires();
        
        // This is a simplified version - in a real implementation,
        // you would need to modify the composer.json file directly
        // or use Composer's internal APIs to modify the constraints
        
        return true;
    }

    private function isDrupalPackage(PackageInterface $package)
    {
        $name = $package->getName();
        return strpos($name, 'drupal/') === 0;
    }

    private function hasStrictConstraints(PackageInterface $package)
    {
        $requires = $package->getRequires();
        
        foreach ($requires as $link) {
            if ($link->getTarget() === 'drupal/core') {
                $constraint = $link->getConstraint();
                // Check if constraint is too strict
                // This is a simplified check - in a real implementation,
                // you would need more sophisticated constraint analysis
                return true;
            }
        }
        
        return false;
    }
}