<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data_Object;

/**
 * Interface Identity Generator
 *
 * @api
 */
interface Identity_Generator_Interface
{
    /**
     * Generate id
     *
     * @return string
     **/
    public function generate_id();
    /**
     * Generate id for data
     *
     * @param string $data
     * @return string
     **/
    public function generate_id_for_data($data);
}