<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block;

/**
 * Base widget class
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Widget extends \Magento\Backend\Block\Template
{
    /**
     * Get ID
     *
     * @return string
     */
    public function get_id()
    {
        if (null === $this->get_data('id')) {
            $this->set_data('id', $this->math_random->get_unique_hash('id_'));
        }
        return $this->get_data('id');
    }
    /**
     * Get HTML ID with specified suffix
     *
     * @param string $suffix
     * @return string
     */
    public function get_suffix_id($suffix)
    {
        return "{$this->get_id()}_{$suffix}";
    }
    /**
     * Get HTML ID
     *
     * @return string
     */
    public function get_html_id()
    {
        return $this->get_id();
    }
    /**
     * Get current url
     *
     * @param array $params url parameters
     * @return string current url
     */
    public function get_current_url($params = [])
    {
        if (!isset($params['_current'])) {
            $params['_current'] = true;
        }
        return $this->get_url('*/*/*', $params);
    }
    /**
     * Prepare Breadcrumbs
     *
     * @param string $label
     * @param string|null $title
     * @param string|null $link
     * @return void
     */
    protected function _add_breadcrumb($label, $title = null, $link = null)
    {
        $this->get_layout()->get_block('breadcrumbs')->add_link($label, $title, $link);
    }
    /**
     * Create button and return its html
     *
     * @param string $label
     * @param string $onclick
     * @param string $class
     * @param string $buttonId
     * @param array $dataAttr
     * @return string
     */
    public function get_button_html($label, $onclick, $class = '', $button_id = null, $data_attr = [])
    {
        return $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => $label, 'onclick' => $onclick, 'class' => $class, 'type' => 'button', 'id' => $button_id])->set_data_attribute($data_attr)->to_html();
    }
}