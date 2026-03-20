<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Accordion;

use Magento\Backend\Block\Widget\Accordion;
/**
 * Accordion item
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Accordion
     */
    protected $_accordion;
    /**
     * Set accordion objet and return self
     *
     * @param Accordion $accordion
     * @return $this
     */
    public function set_accordion($accordion)
    {
        $this->_accordion = $accordion;
        return $this;
    }
    /**
     * Return the target for this item
     *
     * @return string
     */
    public function get_target()
    {
        return $this->get_ajax() ? 'ajax' : '';
    }
    /**
     * Return the HTML title for this item
     *
     * @return string
     */
    public function get_title()
    {
        $title = $this->get_data('title');
        $url = $this->get_content_url() ? $this->get_content_url() : '#';
        $title = '<a href="' . $url . '" class="' . $this->get_target() . '"' . $this->get_ui_id('title-link') . '>' . $title . '</a>';
        return $title;
    }
    /**
     * Return the HTML content for this item
     *
     * @return null|string
     */
    public function get_content()
    {
        $content = $this->get_data('content');
        if (is_string($content)) {
            return $content;
        }
        if ($content instanceof \Magento\Framework\View\Element\Abstract_Block) {
            return $content->to_html();
        }
        return null;
    }
    /**
     * Get the CSS class for this item
     *
     * @return string
     */
    public function get_class()
    {
        $class = $this->get_data('class');
        if ($this->get_open()) {
            $class .= ' open';
        }
        return $class;
    }
    /**
     * Return formatted HTML
     *
     * @return string
     */
    protected function _to_html()
    {
        $content = $this->get_content();
        $html = '<dt id="dt-' . $this->get_html_id() . '" class="' . $this->get_class() . '"';
        $html .= $this->get_ui_id() . '>';
        $html .= $this->get_title();
        $html .= '</dt>';
        $html .= '<dd id="dd-' . $this->get_html_id() . '" class="' . $this->get_class() . '">';
        $html .= $content;
        $html .= '</dd>';
        return $html;
    }
}