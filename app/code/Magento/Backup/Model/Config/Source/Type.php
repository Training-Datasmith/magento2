<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Config\Source;

/**
 * Backups types' source model for system configuration
 *
 * @api
 * @since 100.0.2
 */
class Type implements \Magento\Framework\Option\Array_Interface
{
    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backup_data = null;
    /**
     * @param \Magento\Backup\Helper\Data $backupData
     */
    public function __construct(\Magento\Backup\Helper\Data $backup_data)
    {
        $this->_backup_data = $backup_data;
    }
    /**
     * @inheritDoc
     */
    public function to_option_array()
    {
        $backup_types = [];
        foreach ($this->_backup_data->get_backup_types() as $type => $label) {
            $backup_types[] = ['label' => $label, 'value' => $type];
        }
        return $backup_types;
    }
}