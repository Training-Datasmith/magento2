<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\File\File_List;

use Magento\Framework\View\File\File_List\Collate_Interface;
/**
 * File list collator
 */
class Collator implements Collate_Interface
{
    /**
     * Collate source files
     *
     * @param \Magento\Framework\View\File[] $files
     * @param \Magento\Framework\View\File[] $filesOrigin
     * @return \Magento\Framework\View\File[]
     */
    public function collate($files, $files_origin)
    {
        foreach ($files as $file) {
            $file_id = substr($file->get_file_identifier(), strpos($file->get_file_identifier(), '|'));
            foreach (array_keys($files_origin) as $identifier) {
                if (false !== strpos($identifier, $file_id)) {
                    unset($files_origin[$identifier]);
                }
            }
            $files_origin[$file->get_file_identifier()] = $file;
        }
        return $files_origin;
    }
}