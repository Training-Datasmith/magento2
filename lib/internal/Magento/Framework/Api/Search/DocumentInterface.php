<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\Custom_Attributes_Data_Interface;
/**
 * Interface Search Document
 *
 * @api
 */
interface Document_Interface extends Custom_Attributes_Data_Interface
{
    public const ID = 'id';
    /**
     * @return int
     */
    public function get_id();
    /**
     * @param int $id
     * @return $this
     */
    public function set_id($id);
}