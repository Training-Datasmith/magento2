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
 * Image content validation interface
 *
 * @api
 * @since 100.0.2
 */
interface Image_Content_Validator_Interface
{
    /**
     * Check if gallery entry content is valid
     *
     * @param ImageContentInterface $imageContent
     * @return bool
     * @throws InputException
     */
    public function is_valid(Image_Content_Interface $image_content);
}