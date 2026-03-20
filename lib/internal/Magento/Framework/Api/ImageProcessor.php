<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\Image_Content_Interface;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write_Interface;
use Magento\Framework\Phrase;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Image_Processor implements Image_Processor_Interface, Image_Content_Uploader_Interface
{
    /**
     * @var array
     */
    protected $mime_type_extension_map = ['image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png'];
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Filesystem
     */
    private $content_validator;
    /**
     * @var DataObjectHelper
     */
    private $data_object_helper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var Uploader
     */
    private $uploader;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $media_directory;
    /**
     * @param Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param Uploader $uploader
     */
    public function __construct(Filesystem $file_system, Image_Content_Validator_Interface $content_validator, Data_Object_Helper $data_object_helper, \Psr\Log\Logger_Interface $logger, Uploader $uploader)
    {
        $this->filesystem = $file_system;
        $this->content_validator = $content_validator;
        $this->data_object_helper = $data_object_helper;
        $this->logger = $logger;
        $this->uploader = $uploader;
        $this->media_directory = $this->filesystem->get_directory_write(Directory_List::MEDIA);
    }
    /**
     * @inheritdoc
     */
    public function save(Custom_Attributes_Data_Interface $data_object_with_custom_attributes, $entity_type, ?Custom_Attributes_Data_Interface $previous_customer_data = null)
    {
        //Get all Image related custom attributes
        $image_data_objects = $this->data_object_helper->get_custom_attribute_value_by_type($data_object_with_custom_attributes->get_custom_attributes(), \Magento\Framework\Api\Data\Image_Content_Interface::class);
        // Return if no images to process
        if (empty($image_data_objects)) {
            return $data_object_with_custom_attributes;
        }
        // For every image, save it and replace it with corresponding Eav data object
        /** @var $imageDataObject \Magento\Framework\Api\AttributeValue */
        foreach ($image_data_objects as $image_data_object) {
            /** @var $imageContent \Magento\Framework\Api\Data\ImageContentInterface */
            $image_content = $image_data_object->get_value();
            $filename = $this->process_image_content($entity_type, $image_content);
            //Set filename from static media location into data object
            $data_object_with_custom_attributes->set_custom_attribute($image_data_object->get_attribute_code(), $filename);
            //Delete previously saved image if it exists
            if ($previous_customer_data) {
                $previous_image_attribute = $previous_customer_data->get_custom_attribute($image_data_object->get_attribute_code());
                if ($previous_image_attribute) {
                    $previous_image_path = $previous_image_attribute->get_value();
                    if (!empty($previous_image_path) && $previous_image_path != $filename) {
                        @unlink($this->media_directory->get_absolute_path() . $entity_type . $previous_image_path);
                    }
                }
            }
        }
        return $data_object_with_custom_attributes;
    }
    /**
     * @inheritdoc
     */
    public function process_image_content($entity_type, $image_content)
    {
        $tmp_file_name = $this->save_to_tmp_dir($image_content);
        try {
            return $this->move_from_tmp_dir($image_content, $tmp_file_name, $this->media_directory, (string) $entity_type);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return '';
    }
    /**
     * @inheritDoc
     */
    public function save_to_tmp_dir(Image_Content_Interface $image_content, bool $validate = true): string
    {
        if ($validate && !$this->content_validator->is_valid($image_content)) {
            throw new Input_Exception(new Phrase('The image content is invalid. Verify the content and try again.'));
        }
        $file_content = @base64_decode($image_content->get_base64encoded_data(), true);
        $tmp_directory = $this->filesystem->get_directory_write(Directory_List::SYS_TMP);
        $file_name = $this->get_file_name($image_content);
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $tmp_file_name = substr(md5(rand()), 0, 7) . '.' . $file_name;
        $tmp_directory->write_file($tmp_file_name, $file_content);
        return $tmp_file_name;
    }
    /**
     * @inheritDoc
     */
    public function move_from_tmp_dir(Image_Content_Interface $image_content, string $tmp_file_name, Write_Interface $destination_directory, ?string $destination_path = null, ?string $file_name = null, int $flags = 0): ?string
    {
        $flags = $flags ?: self::CASE_SENSITIVE | self::PATH_DISPERSION | self::RENAME_IF_EXIST;
        $file_name = $file_name ?? $this->get_file_name($image_content);
        $tmp_directory = $this->filesystem->get_directory_write(Directory_List::SYS_TMP);
        $this->uploader->process_file_attributes(['tmp_name' => $tmp_directory->get_absolute_path() . $tmp_file_name, 'name' => $file_name]);
        $this->uploader->set_files_dispersion((bool) ($flags & self::PATH_DISPERSION));
        // setFilenamesCaseSensitivity is actually setting whether the filenames are case-insensitive,
        // meaning that passing TRUE makes the filenames case-insensitive and vice versa.
        $this->uploader->set_filenames_case_sensitivity(!($flags & self::CASE_SENSITIVE));
        $this->uploader->set_allow_rename_files((bool) ($flags & self::RENAME_IF_EXIST));
        $this->uploader->save($this->media_directory->get_absolute_path($destination_path), $file_name);
        return $this->uploader->get_uploaded_file_name();
    }
    /**
     * Get mime type extension
     *
     * @param string $mimeType
     * @return string
     */
    protected function get_mime_type_extension($mime_type)
    {
        return $this->mime_type_extension_map[$mime_type] ?? '';
    }
    /**
     * Get file name
     *
     * @param ImageContentInterface $imageContent
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function get_file_name($image_content)
    {
        $file_name = $image_content->get_name();
        if (!pathinfo($file_name, PATHINFO_EXTENSION)) {
            if (!$image_content->get_type() || !$this->get_mime_type_extension($image_content->get_type())) {
                throw new Input_Exception(new Phrase('Cannot recognize image extension.'));
            }
            $file_name .= '.' . $this->get_mime_type_extension($image_content->get_type());
        }
        return $file_name;
    }
}