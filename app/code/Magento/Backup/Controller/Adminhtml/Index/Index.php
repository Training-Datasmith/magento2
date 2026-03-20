<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Index extends \Magento\Backup\Controller\Adminhtml\Index implements Http_Get_Action_Interface
{
    /**
     * Backup list action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->get_request()->get_param('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->load_layout();
        $this->_set_active_menu('Magento_Backup::system_tools_backup');
        $this->_view->get_page()->get_config()->get_title()->prepend(__('Backups'));
        $this->_add_breadcrumb(__('System'), __('System'));
        $this->_add_breadcrumb(__('Tools'), __('Tools'));
        $this->_add_breadcrumb(__('Backups'), __('Backup'));
        $this->_view->render_layout();
    }
}