<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Composer;

/**
 * Class to run composer remove command
 */
class Remove
{
    /**
     * Composer application factory
     *
     * @var MagentoComposerApplicationFactory
     */
    private $composer_application_factory;
    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerApplicationFactory
     */
    public function __construct(Magento_Composer_Application_Factory $composer_application_factory)
    {
        $this->composer_application_factory = $composer_application_factory;
    }
    /**
     * Run 'composer remove'
     *
     * @param array $packages
     * @throws \Exception
     *
     * @return string
     */
    public function remove(array $packages)
    {
        $composer_application = $this->composer_application_factory->create();
        return $composer_application->run_composer_command(['command' => 'remove', 'packages' => $packages, '--no-update-with-dependencies' => true]);
    }
}