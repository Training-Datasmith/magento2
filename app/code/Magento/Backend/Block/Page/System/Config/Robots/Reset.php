<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Page\System\Config\Robots;

use Magento\Framework\App\Config\Scope_Config_Interface;
/**
 * "Reset to Defaults" button renderer
 *
 * @deprecated 100.1.6
 * @see Nothing
 */
class Reset extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Page robots default instructions
     */
    public const XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS = 'design/search_engine_robots/default_custom_instructions';
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_template('Magento_Config::page/system/config/robots/reset.phtml');
    }
    /**
     * Get robots.txt custom instruction default value
     *
     * @return string
     */
    public function get_robots_default_custom_instructions()
    {
        return trim((string) $this->_scope_config->get_value(self::XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS, Scope_Config_Interface::SCOPE_TYPE_DEFAULT));
    }
    /**
     * Generate button html
     *
     * @return string
     */
    public function get_button_html()
    {
        $button = $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['id' => 'reset_to_default_button', 'label' => __('Reset to Default'), 'onclick' => 'javascript:resetRobotsToDefault(); return false;']);
        return $button->to_html();
    }
    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        // Remove scope label
        $element->uns_scope()->uns_can_use_website_value()->uns_can_use_default_value();
        return parent::render($element);
    }
    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _get_element_html(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        return $this->_to_html();
    }
}