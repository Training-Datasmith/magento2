<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Button;

/**
 * @api
 * @method string getButtonKey()
 * @method string getRegion()
 * @method string getName()
 * @method int getLevel()
 * @method int getSortOrder()
 * @method string getTitle()
 * @since 100.0.2
 */
class Item extends \Magento\Framework\Data_Object
{
    /**
     * Object delete flag
     *
     * @var bool
     */
    protected $_is_deleted = false;
    /**
     * Set _isDeleted flag value (if $isDeleted parameter is defined) and return current flag value
     *
     * @param boolean $isDeleted
     * @return bool
     */
    public function is_deleted($is_deleted = null)
    {
        $result = $this->_is_deleted;
        if ($is_deleted !== null) {
            $this->_is_deleted = $is_deleted;
        }
        return $result;
    }
}