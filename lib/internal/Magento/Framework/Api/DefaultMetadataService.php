<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

/**
 * Default implementation of metadata service, which does not return any real attributes.
 */
class Default_Metadata_Service implements Metadata_Service_Interface
{
    /**
     * {@inheritdoc}
     */
    public function get_custom_attributes_metadata($data_object_class_name = null)
    {
        return [];
    }
}