<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Service\V1;

/**
 * Interface for module service.
 * @api
 * @since 100.0.2
 */
interface Module_Service_Interface
{
    /**
     * Returns an array of enabled modules
     *
     * @return string[]
     */
    public function get_modules();
}