<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\Link_Interface;
/**
 * Represents link with collected data and initialized vector for decryption.
 */
class Link implements Link_Interface
{
    /**
     * @param string $url
     * @param string $initializationVector
     */
    public function __construct(private $url, private $initialization_vector)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_url()
    {
        return $this->url;
    }
    /**
     * @inheritdoc
     */
    public function get_initialization_vector()
    {
        return $this->initialization_vector;
    }
}