<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

use Composer\Console\Application_Factory;
use Magento\Framework\App\Filesystem\Directory_List;
use Symfony\Component\Console\Input\Array_Input;
use Symfony\Component\Console\Output\Buffered_Output;
/**
 * A class to check if there are any dependency to package(s) that exists in the codebase, regardless of package type
 */
class Dependency_Checker
{
    /**
     * @var ApplicationFactory
     */
    private $application_factory;
    /**
     * @var DirectoryList
     */
    private $directory_list;
    /**
     * Constructor
     *
     * @param ApplicationFactory $applicationFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(Application_Factory $application_factory, Directory_List $directory_list)
    {
        $this->application_factory = $application_factory;
        $this->directory_list = $directory_list;
    }
    /**
     * Checks dependencies to package(s), returns array of dependencies in the format of
     * 'package A' => [array of package names depending on package A]
     * If $excludeSelf is set to true, items in $packages will be excluded in all
     * "array of package names depending on package A"
     *
     * @param string[] $packages
     * @param bool $excludeSelf
     * @return string[]
     */
    public function check_dependencies(array $packages, $exclude_self = false)
    {
        $app = $this->application_factory->create();
        $app->set_auto_exit(false);
        $dependencies = [];
        foreach ($packages as $package) {
            $buffer = new Buffered_Output();
            $app->reset_composer();
            $app->run(new Array_Input(['command' => 'depends', '--working-dir' => $this->directory_list->get_root(), 'package' => $package]), $buffer);
            $depending_packages = $this->parse_composer_output($buffer->fetch());
            if ($exclude_self === true) {
                $depending_packages = array_values(array_diff($depending_packages, $packages));
            }
            $dependencies[$package] = $depending_packages;
        }
        return $dependencies;
    }
    /**
     * Parse output from running composer remove command into an array of depending packages
     *
     * @param string $output
     * @return string[]
     */
    private function parse_composer_output($output)
    {
        $raw_lines = explode(PHP_EOL, $output);
        $packages = [];
        foreach ($raw_lines as $raw_line) {
            $parts = explode(' ', $raw_line);
            if (count(explode('/', $parts[0])) == 2) {
                if (strpos($parts[0], 'magento/project-') === false) {
                    $packages[] = $parts[0];
                }
            }
        }
        return $packages;
    }
}