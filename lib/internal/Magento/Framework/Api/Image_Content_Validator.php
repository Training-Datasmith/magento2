<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\Image_Content_Interface;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Phrase;
/**
 * Class for Image content validation
 */
class Image_Content_Validator implements Image_Content_Validator_Interface
{
    /**
     * @var array
     */
    private $default_mime_types = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'];
    /**
     * @var array
     */
    private $allowed_mime_types;
    /**
     * @param array $allowedMimeTypes
     */
    public function __construct(array $allowed_mime_types = [])
    {
        $this->allowed_mime_types = array_merge($this->default_mime_types, $allowed_mime_types);
    }
    /**
     * Check if gallery entry content is valid
     *
     * @param ImageContentInterface $imageContent
     * @return bool
     * @throws InputException
     */
    public function is_valid(Image_Content_Interface $image_content)
    {
        $file_content = @base64_decode($image_content->get_base64encoded_data(), true);
        if (empty($file_content)) {
            throw new Input_Exception(new Phrase('The image content must be valid base64 encoded data.'));
        }
        $image_properties = @getimagesizefromstring($file_content);
        if (empty($image_properties)) {
            throw new Input_Exception(new Phrase('The image content must be valid base64 encoded data.'));
        }
        $source_mime_type = $image_properties['mime'];
        if ($source_mime_type != $image_content->get_type() || !$this->is_mime_type_valid($source_mime_type)) {
            throw new Input_Exception(new Phrase('The image MIME type is not valid or not supported.'));
        }
        if (!$this->is_name_valid($image_content->get_name())) {
            throw new Input_Exception(new Phrase('Provided image name contains forbidden characters.'));
        }
        return true;
    }
    /**
     * Check if given mime type is valid
     *
     * @param string $mimeType
     * @return bool
     */
    protected function is_mime_type_valid($mime_type)
    {
        return in_array($mime_type, $this->allowed_mime_types);
    }
    /**
     * Check if given filename is valid
     *
     * @param string $name
     * @return bool
     */
    protected function is_name_valid($name)
    {
        // Cannot contain \ / ? * : " ; < > ( ) | { }
        if ($name === null || !preg_match('/^[^\/?*:";<>()|{}\\\\]+$/', $name)) {
            return false;
        }
        return true;
    }
}