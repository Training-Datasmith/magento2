<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\Exception\File_System_Exception;
/**
 * Interface for parsing comments in the configuration file.
 *
 * @api
 */
interface Comment_Parser_Interface
{
    /**
     * Retrieve config list from file comments.
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     */
    public function execute($file_name);
}