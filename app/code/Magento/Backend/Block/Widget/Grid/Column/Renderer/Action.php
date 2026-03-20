<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Grid column widget for rendering action grid cells
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @see don't recommend this approach in favour of UI component implementation
 * @since 100.0.2
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_json_encoder;
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_html_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     * @param Random|null $random
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, array $data = [], ?Secure_Html_Renderer $secure_html_renderer = null, ?Random $random = null)
    {
        $this->_json_encoder = $json_encoder;
        parent::__construct($context, $data);
        $this->secure_html_renderer = $secure_html_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
    }
    /**
     * Renders column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\Data_Object $row)
    {
        $actions = $this->get_column()->get_actions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        if (count($actions) == 1 && !$this->get_column()->get_no_link()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_to_link_html($action, $row);
                }
            }
        }
        $out = '<select class="admin__control-select" onchange="varienGridAction.execute(this);">' . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            if (is_array($action)) {
                $out .= $this->_to_option_html($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }
    /**
     * Render single action as dropdown option html
     *
     * @param array $action
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _to_option_html($action, \Magento\Framework\Data_Object $row)
    {
        $action_attributes = new \Magento\Framework\Data_Object();
        $action_caption = '';
        $this->_transform_action_data($action, $action_caption, $row);
        $html_attributes = ['value' => $this->escape_html_attr($this->_json_encoder->encode($action), false)];
        $action_attributes->set_data($html_attributes);
        return '<option ' . $action_attributes->serialize() . '>' . $action_caption . '</option>';
    }
    /**
     * Render single action as link html
     *
     * @param array $action
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _to_link_html($action, \Magento\Framework\Data_Object $row)
    {
        $action_attributes = new \Magento\Framework\Data_Object();
        $action_caption = '';
        $this->_transform_action_data($action, $action_caption, $row);
        if (isset($action['confirm'])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $action['onclick'] = 'return window.confirm(\'' . addslashes($this->escape_html($action['confirm'])) . '\')';
            unset($action['confirm']);
        }
        if (empty($action['id'])) {
            $action['id'] = 'id' . $this->random->get_random_string(10);
        }
        $action_attributes->set_data($action);
        $onclick = $action_attributes->get_data('onclick');
        $style = $action_attributes->get_data('style');
        $action_attributes->unset_data(['onclick', 'style']);
        $html = '<a ' . $action_attributes->serialize() . '>' . $action_caption . '</a>';
        if ($onclick) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $onclick = html_entity_decode($onclick);
            $html .= $this->secure_html_renderer->render_event_listener_as_tag('onclick', $onclick, "#{$action['id']}");
        }
        if ($style) {
            $html .= $this->secure_html_renderer->render_style_as_tag($style, "#{$action['id']}");
        }
        return $html;
    }
    /**
     * Prepares action data for html render
     *
     * @param &array $action
     * @param &string $actionCaption
     * @param \Magento\Framework\DataObject $row
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _transform_action_data(&$action, &$action_caption, \Magento\Framework\Data_Object $row)
    {
        foreach ($action as $attribute => $value) {
            if (isset($action[$attribute]) && !is_array($action[$attribute])) {
                $this->get_column()->set_format($action[$attribute]);
                $action[$attribute] = parent::render($row);
            } else {
                $this->get_column()->set_format(null);
            }
            switch ($attribute) {
                case 'caption':
                    $action_caption = $action['caption'];
                    unset($action['caption']);
                    break;
                case 'url':
                    if (is_array($action['url']) && isset($action['field'])) {
                        $params = [$action['field'] => $this->_get_value($row)];
                        if (isset($action['url']['params'])) {
                            $params[] = $action['url']['params'];
                        }
                        $action['href'] = $this->get_url($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }
                    unset($action['url']);
                    break;
                case 'popup':
                    $action['onclick'] = 'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,' . 'scrollbars=1\');return false;';
                    break;
            }
        }
        return $this;
    }
}