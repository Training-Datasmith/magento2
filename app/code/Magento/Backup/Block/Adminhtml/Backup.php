<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Block\Adminhtml;

use Magento\Framework\View\Element\Abstract_Block;
/**
 * Adminhtml backup page content block
 *
 * @api
 * @since 100.0.2
 */
class Backup extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backup::backup/list.phtml';
    /**
     * Prepare the layout
     *
     * @return AbstractBlock|void
     */
    protected function _prepare_layout()
    {
        parent::_prepare_layout();
        $this->get_toolbar()->add_child('createSnapshotButton', \Magento\Backend\Block\Widget\Button::class, ['label' => __('System Backup'), 'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT . "')", 'class' => 'primary system-backup']);
        $this->get_toolbar()->add_child('createMediaBackupButton', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Database and Media Backup'), 'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_MEDIA . "')", 'class' => 'primary database-media-backup']);
        $this->get_toolbar()->add_child('createButton', \Magento\Backend\Block\Widget\Button::class, ['label' => __('Database Backup'), 'onclick' => "return backup.backup('" . \Magento\Framework\Backup\Factory::TYPE_DB . "')", 'class' => 'task primary database-backup']);
        $this->add_child('dialogs', \Magento\Backup\Block\Adminhtml\Dialogs::class);
    }
    /**
     * Return HTML for the backups grid
     *
     * @return string
     */
    public function get_grid_html()
    {
        return $this->get_child_html('backupsGrid');
    }
    /**
     * Generate html code for pop-up messages that will appear when user click on "Rollback" link
     *
     * @return string
     */
    public function get_dialogs_html()
    {
        return $this->get_child_html('dialogs');
    }
}