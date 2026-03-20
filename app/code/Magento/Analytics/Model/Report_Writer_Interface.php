<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Model;

use Magento\Framework\Filesystem\Directory\Write_Interface;
/**
 * Interface ReportWriterInterface
 *
 * Writes report files
 * Executes export of collected data
 * Iterates registered providers @see etc/analytics.xml
 * Collects data (to TMP folder)
 *
 * @api
 */
interface Report_Writer_Interface
{
    /**
     * Writes report files to provided path
     *
     * @param string $path
     * @return void
     */
    public function write(Write_Interface $directory, $path);
}