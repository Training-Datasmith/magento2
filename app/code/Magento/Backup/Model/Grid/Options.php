<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Grid;

/**
 * Backup types option array
 *
 * @api
 * @since 100.0.2
 */
class Options implements \Magento\Framework\Option\Array_Interface
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_helper;
    /**
     * @param \Magento\Backup\Helper\Data $backupHelper
     */
    public function __construct(\Magento\Backup\Helper\Data $backup_helper)
    {
        $this->_helper = $backup_helper;
    }
    /**
     * Return backup types array
     *
     * @return array
     */
    public function to_option_array()
    {
        return $this->_helper->get_backup_types();
    }
}