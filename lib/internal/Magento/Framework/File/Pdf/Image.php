<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\File\Pdf;

use Magento\Framework\File\Pdf\Image_Resource\Image_Factory;
class Image
{
    /**
     * @var \Magento\Framework\File\Pdf\ImageResource\ImageFactory
     */
    private Image_Factory $image_factory;
    /**
     * @param \Magento\Framework\File\Pdf\ImageResource\ImageFactory $imageFactory
     */
    public function __construct(Image_Factory $image_factory)
    {
        $this->image_factory = $image_factory;
    }
    /**
     * Filepath of image file
     *
     * @param string $filePath
     * @return \Zend_Pdf_Resource_Image|\Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function image_with_path_advanced(string $file_path)
    {
        return $this->image_factory->factory($file_path);
    }
}