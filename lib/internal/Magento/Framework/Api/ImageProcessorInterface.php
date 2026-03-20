<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\Image_Content_Interface;
use Magento\Framework\Exception\Input_Exception;
/**
 * Interface ImageProcessorInterface
 *
 * @api
 * @since 100.0.2
 */
interface Image_Processor_Interface
{
    /**
     * Process Data objects with image type custom attributes and update custom attribute values with saved image paths
     *
     * @param CustomAttributesDataInterface $dataObjectWithCustomAttributes
     * @param string $entityType entity type
     * @param CustomAttributesDataInterface $previousCustomerData
     * @return CustomAttributesDataInterface
     */
    public function save(Custom_Attributes_Data_Interface $data_object_with_custom_attributes, $entity_type, ?Custom_Attributes_Data_Interface $previous_customer_data = null);
    /**
     * Process image and save it to the entity's media directory
     *
     * @param string $entityType
     * @param ImageContentInterface $imageContent
     * @return string Relative path of the file where image was saved
     * @throws InputException
     */
    public function process_image_content($entity_type, $image_content);
}