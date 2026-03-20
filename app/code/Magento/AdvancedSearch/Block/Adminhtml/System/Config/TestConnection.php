<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Block\Adminhtml\System\Config;

/**
 * Search engine test connection block
 * @api
 * @since 100.1.0
 */
class Test_Connection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Set template to itself
     *
     * @return $this
     * @since 100.1.0
     */
    protected function _prepare_layout(): static
    {
        parent::_prepare_layout();
        $this->set_template('Magento_AdvancedSearch::system/config/testconnection.phtml');
        return $this;
    }
    /**
     * Unset some non-related element parameters
     *
     * @return string
     * @since 100.1.0
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $element = clone $element;
        $element->uns_scope()->uns_can_use_website_value()->uns_can_use_default_value();
        return parent::render($element);
    }
    /**
     * Get the button and scripts contents
     *
     * @return string
     * @since 100.1.0
     */
    protected function _get_element_html(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $original_data = $element->get_original_data();
        $this->add_data(['button_label' => __($original_data['button_label']), 'html_id' => $element->get_html_id(), 'ajax_url' => $this->_url_builder->get_url('catalog/search_system_config/testconnection'), 'field_mapping' => str_replace('"', '\"', json_encode($this->_get_field_mapping()))]);
        return $this->_to_html();
    }
    /**
     * Returns configuration fields required to perform the ping request
     *
     * @since 100.1.0
     */
    protected function _get_field_mapping(): array
    {
        return ['engine' => 'catalog_search_engine'];
    }
}