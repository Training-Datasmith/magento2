<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted data.
 */
class EncodedContext
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
        private $initializationVector = ''
    ) {
    }

    /**
     * Retrieve content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Retrieve initialization vector
     *
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
