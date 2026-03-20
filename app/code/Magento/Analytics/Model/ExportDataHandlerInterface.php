<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Model;

/**
 * The interface represents the type of classes that handling of a new data collection for MBI.
 *
 * @api
 */
interface Export_Data_Handler_Interface
{
    /**
     * Execute collecting new data for MBI.
     *
     * @return bool
     */
    public function prepare_export_data();
}