<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

/**
 * Feed interface
 *
 * @api
 */
interface Feed_Interface
{
    /**
     * Returns the formatted feed content
     *
     * @return string
     */
    public function get_formatted_content(): string;
}