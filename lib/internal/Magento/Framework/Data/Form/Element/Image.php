<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\Url_Interface;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Category form input image element
 *
 * @api
 */
class Image extends Abstract_Element
{
    /**
     * @var UrlInterface
     */
    protected $_url_builder;
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, Url_Interface $url_builder, $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $random = $random ?? Object_Manager::get_instance()->get(Random::class);
        $this->_url_builder = $url_builder;
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer, $random);
        $this->set_type('file');
        $this->secure_renderer = $secure_renderer;
        $this->random = $random;
    }
    /**
     * Return element html code
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = '';
        if ((string) $this->get_escaped_value()) {
            $url = $this->_get_url();
            if (!preg_match("/^http\\:\\/\\/|https\\:\\/\\//", $url)) {
                $url = $this->_url_builder->get_base_url(['_type' => Url_Interface::URL_TYPE_MEDIA]) . $url;
            }
            $link_id = 'linkId' . $this->random->get_random_string(8);
            $html = '<a previewlinkid="' . $link_id . '" href="' . $url . '" ' . $this->_get_ui_id('link') . '>' . '<img src="' . $url . '" id="' . $this->get_html_id() . '_image" title="' . $this->get_escaped_value() . '"' . ' alt="' . $this->get_escaped_value() . '" height="22" width="22" class="small-image-preview v-middle"  ' . $this->_get_ui_id() . ' />' . '</a> ';
            $html .= $this->secure_renderer->render_event_listener_as_tag('onclick', "imagePreview('{$this->get_html_id()}_image');\nreturn false;", "*[previewlinkid='{$link_id}']");
        }
        $this->set_class('input-file');
        $html .= parent::get_element_html();
        $html .= $this->_get_delete_checkbox();
        return $html;
    }
    /**
     * Return html code of delete checkbox element
     *
     * @return string
     */
    protected function _get_delete_checkbox()
    {
        $html = '';
        if ($this->get_escaped_value()) {
            $label = (string) new \Magento\Framework\Phrase('Delete Image');
            $html .= '<span class="delete-image">';
            $html .= '<input type="checkbox"' . ' name="' . parent::get_name() . '[delete]" value="1" class="checkbox"' . ' id="' . $this->get_html_id() . '_delete"' . ($this->get_disabled() ? ' disabled="disabled"' : '') . '/>';
            $html .= '<label for="' . $this->get_html_id() . '_delete"' . ($this->get_disabled() ? ' class="disabled"' : '') . '> ' . $label . '</label>';
            $html .= $this->_get_hidden_input();
            $html .= '</span>';
        }
        return $html;
    }
    /**
     * Return html code of hidden element
     *
     * @return string
     */
    protected function _get_hidden_input()
    {
        return '<input type="hidden" name="' . parent::get_name() . '[value]" value="' . $this->get_escaped_value() . '" />';
    }
    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _get_url()
    {
        return $this->get_escaped_value();
    }
    /**
     * Return name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->get_data('name');
    }
}