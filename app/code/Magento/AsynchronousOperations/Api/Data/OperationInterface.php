<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Api\Data;

/**
 * Class OperationInterface
 * @api
 * @since 100.2.0
 */
interface Operation_Interface extends \Magento\Framework\Bulk\Operation_Interface
{
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterface|null
     * @since 100.2.0
     */
    public function get_extension_attributes();
    /**
     * Set an extension attributes object.
     *
     * @return $this
     * @since 100.2.0
     */
    public function set_extension_attributes(\Magento\Asynchronous_Operations\Api\Data\Operation_Extension_Interface $extension_attributes);
}