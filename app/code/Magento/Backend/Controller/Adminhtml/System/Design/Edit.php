<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

class Edit extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_active_menu('Magento_Backend::system_design_schedule');
        $result_page->get_config()->get_title()->prepend(__('Store Design'));
        $id = (int) $this->get_request()->get_param('id');
        $design = $this->_object_manager->create(\Magento\Framework\App\Design_Interface::class);
        if ($id) {
            $design->load($id);
        }
        $result_page->get_config()->get_title()->prepend($design->get_id() ? __('Edit Store Design Change') : __('New Store Design Change'));
        $this->_core_registry->register('design', $design);
        $result_page->add_content($result_page->get_layout()->create_block(\Magento\Backend\Block\System\Design\Edit::class));
        $result_page->add_left($result_page->get_layout()->create_block(\Magento\Backend\Block\System\Design\Edit\Tabs::class, 'design_tabs'));
        return $result_page;
    }
}