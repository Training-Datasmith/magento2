<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Design\Theme\Customization\File;

/**
 * Theme JS file service class
 */
class Js extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * File type customization
     */
    public const TYPE = 'js';

    public const CONTENT_TYPE = 'js';

    /**#@-*/

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }
}
