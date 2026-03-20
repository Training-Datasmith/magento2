<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Design\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id('design_tabs');
        $this->set_dest_element_id('design-edit-form');
        $this->set_title(__('Design Change'));
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepare_layout()
    {
        $this->add_tab('general', ['label' => __('General'), 'content' => $this->get_layout()->create_block(\Magento\Backend\Block\System\Design\Edit\Tab\General::class)->to_html()]);
        return parent::_prepare_layout();
    }
}