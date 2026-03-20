<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Backup;

/**
 * Interface for work with archives
 *
 * @api
 */
interface Source_File_Interface
{
    /**
     * Check if keep files of backup
     *
     * @return bool
     */
    public function keep_source_file();
    /**
     * Set if keep files of backup
     *
     * @param bool $keepSourceFile
     * @return $this
     */
    public function set_keep_source_file(bool $keep_source_file);
}