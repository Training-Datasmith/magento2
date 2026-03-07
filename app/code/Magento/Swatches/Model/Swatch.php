<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Swatches\Model;

/**
 * @api
 * @since 100.0.2
 */
class Swatch extends \Magento\Framework\Model\AbstractModel
{
    /** Constant for identifying attribute frontend type for textual swatch */
    public const SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT = 'swatch_text';

    /** Constant for identifying attribute frontend type for visual swatch */
    public const SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT = 'swatch_visual';

    /** Swatch input type key in array to retrieve the value */
    public const SWATCH_INPUT_TYPE_KEY = 'swatch_input_type';

    /** Value for text swatch input type */
    public const SWATCH_INPUT_TYPE_TEXT = 'text';

    /** Value for visual swatch input type */
    public const SWATCH_INPUT_TYPE_VISUAL = 'visual';

    /** Value for dropdown input type */
    public const SWATCH_INPUT_TYPE_DROPDOWN = 'dropdown';

    /** Constant for identifying textual swatch type */
    public const SWATCH_TYPE_TEXTUAL = 0;

    /** Constant for identifying visual swatch type with color number value */
    public const SWATCH_TYPE_VISUAL_COLOR = 1;

    /** Constant for identifying visual swatch type with color number value */
    public const SWATCH_TYPE_VISUAL_IMAGE = 2;

    /** Constant for identifying empty swatch type */
    public const SWATCH_TYPE_EMPTY = 3;

    /**
     * Name of swatch image
     */
    public const SWATCH_IMAGE_NAME = 'swatch_image';

    /**
     * Name of swatch thumbnail
     */
    public const SWATCH_THUMBNAIL_NAME = 'swatch_thumb';

    /**
     * Initialize resource model
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Swatches\Model\ResourceModel\Swatch::class);
    }
}
