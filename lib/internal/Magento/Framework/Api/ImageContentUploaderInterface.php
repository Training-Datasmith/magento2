<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\Image_Content_Interface;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filesystem\Directory\Write_Interface;
interface Image_Content_Uploader_Interface extends Image_Processor_Interface
{
    public const CASE_SENSITIVE = 1;
    public const PATH_DISPERSION = 2;
    public const RENAME_IF_EXIST = 4;
    /**
     * Move image content to a temp directory.
     *
     * @param ImageContentInterface $imageContent
     * @param bool $validate
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function save_to_tmp_dir(Image_Content_Interface $image_content, bool $validate = true): string;
    /**
     * Move image content from temp to the specified directory.
     *
     * @param ImageContentInterface $imageContent
     * @param string $tmpFileName
     * @param WriteInterface $destinationDirectory
     * @param string|null $destinationPath
     * @param string|null $fileName
     * @param int $flags Flags is a bitmask that controls the operations that can be performed on the file.
     * The default value is 0 meaning self::CASE_SENSITIVE | self::PATH_DISPERSION | self::RENAME_IF_EXIST.
     * @return string|null
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function move_from_tmp_dir(Image_Content_Interface $image_content, string $tmp_file_name, Write_Interface $destination_directory, ?string $destination_path = null, ?string $file_name = null, int $flags = 0): ?string;
}