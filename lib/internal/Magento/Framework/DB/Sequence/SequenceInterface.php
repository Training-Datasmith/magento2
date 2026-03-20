<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Sequence;

/**
 * Interface represents sequence
 *
 * @api
 */
interface Sequence_Interface
{
    /**
     * Retrieve current value
     *
     * @return string
     */
    public function get_current_value();
    /**
     * Retrieve next value
     *
     * @return string
     */
    public function get_next_value();
}