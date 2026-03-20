<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Deployment_Config\Writer;

/**
 * Interface \Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface
 *
 * @api
 */
interface Formatter_Interface
{
    /**
     * Format deployment configuration
     *
     * @param array $data
     * @param array $comments
     * @return string
     */
    public function format($data, array $comments = []);
}