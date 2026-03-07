<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 *  Theme customization service class for custom css
 */

namespace Magento\Theme\Model\Theme\Customization\File;

class CustomCss extends \Magento\Framework\View\Design\Theme\Customization\AbstractFile
{
    /**#@+
     * Custom CSS file type customization
     */
    public const TYPE = 'custom_css';

    public const CONTENT_TYPE = 'css';

    /**#@-*/

    /**
     * Default filename
     */
    public const FILE_NAME = 'custom.css';

    /**
     * Default order position
     */
    public const SORT_ORDER = 10;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareFileName(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        if (!$file->getFileName()) {
            $file->setFileName(self::FILE_NAME);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSortOrder(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $file->setData('sort_order', self::SORT_ORDER);
    }
}
