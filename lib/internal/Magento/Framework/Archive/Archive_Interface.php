<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

/**
 * @api
 * @since 100.0.2
 */
interface Archive_Interface
{
    /**
     * Pack file or directory.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function pack($source, $destination);
    /**
     * Unpack file or directory.
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function unpack($source, $destination);
}