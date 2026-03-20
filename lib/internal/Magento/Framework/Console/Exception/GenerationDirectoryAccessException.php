<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Console\Exception;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Phrase;
/**
 * The default exception for missing write permissions on compilation generated folder.
 */
class Generation_Directory_Access_Exception extends File_System_Exception
{
    /**
     * @inheritdoc
     */
    public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        $phrase = $phrase ?: new Phrase('Command line user does not have read and write permissions on ' . $this->get_default_directory_path(Directory_List::GENERATED) . ' directory. ' . 'Please address this issue before using Magento command line.');
        parent::__construct($phrase, $cause, $code);
    }
    /**
     * Get default directory path by code
     *
     * @param string $code
     * @return string
     */
    private function get_default_directory_path($code)
    {
        $config = Directory_List::get_default_config();
        $result = '';
        if (isset($config[$code][Directory_List::PATH])) {
            $result = $config[$code][Directory_List::PATH];
        }
        return $result;
    }
}