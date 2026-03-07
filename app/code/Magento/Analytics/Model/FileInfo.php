<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted file.
 */
class FileInfo
{
    /**
     * @param string $path
     * @param string $initializationVector
     */
    public function __construct(
        /**
         * Relative path to an encrypted file.
         */
        private $path = '',
        /**
         * Initialization vector that was used for encryption.
         */
        private $initializationVector = ''
    ) {
    }

    /**
     * Retrieve path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
