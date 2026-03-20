<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted data.
 */
class Encoded_Context
{
    /**
     * @param string $content
     * @param string $initializationVector
     */
    public function __construct(
        /**
         * Encrypted string.
         */
        private $content,
        /**
         * Initialization vector that was used for encryption.
         */
        private $initialization_vector = ''
    )
    {
    }
    /**
     * Retrieve content
     *
     * @return string
     */
    public function get_content()
    {
        return $this->content;
    }
    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function get_initialization_vector()
    {
        return $this->initialization_vector;
    }
}