<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Form editor element
 */
class Editor extends Textarea
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * Editor constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param Random|null $random
     * @param SecureHtmlRenderer|null $secureRenderer
     * @throws \RuntimeException
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?\Magento\Framework\Serialize\Serializer\Json $serializer = null, ?Random $random = null, ?Secure_Html_Renderer $secure_renderer = null)
    {
        parent::__construct($factory_element, $factory_collection, $escaper, $data);
        if ($this->is_enabled()) {
            $this->set_type('wysiwyg');
            $this->set_ext_type('wysiwyg');
        } else {
            $this->set_type('textarea');
            $this->set_ext_type('textarea');
        }
        $this->serializer = $serializer ?? Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * Returns buttons translation
     *
     * @return array
     */
    protected function get_button_translations()
    {
        $button_translations = ['Insert Image...' => $this->translate('Insert Image...'), 'Insert Media...' => $this->translate('Insert Media...'), 'Insert File...' => $this->translate('Insert File...')];
        return $button_translations;
    }
    /**
     * Returns JS config
     *
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    protected function get_json_config()
    {
        if (is_object($this->get_config()) && method_exists($this->get_config(), 'toJson')) {
            return $this->get_config()->to_json();
        } else {
            return $this->serializer->serialize($this->get_config());
        }
    }
    /**
     * Fetch config options from plugin.  If $key is passed, return only that option key's value
     *
     * @param string $pluginName
     * @param string|null $key
     * @return mixed all options or single option if $key is passed; null if nonexistent
     */
    public function get_plugin_config_options($plugin_name, $key = null)
    {
        if (!is_array($this->get_config('plugins'))) {
            return null;
        }
        $plugins = $this->get_config('plugins');
        $plugin_arr_index = array_search($plugin_name, array_column($plugins, 'name'));
        if ($plugin_arr_index === false || !isset($plugins[$plugin_arr_index]['options'])) {
            return null;
        }
        $plugin_options = $plugins[$plugin_arr_index]['options'];
        if ($key !== null) {
            return $plugin_options[$key] ?? null;
        } else {
            return $plugin_options;
        }
    }
    /**
     * Returns element html
     *
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function get_element_html()
    {
        $js = $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], <<<script
                        //<![CDATA[
                        openEditorPopup = function(url, name, specs, parent) {
                            if ((typeof popups == "undefined") || popups[name] == undefined || popups[name].closed) {
                                if (typeof popups == "undefined") {
                                    popups = new Array();
                                }
                                var opener = (parent != undefined ? parent : window);
                                popups[name] = opener.open(url, name, specs);
                            } else {
                                popups[name].focus();
                            }
                            return popups[name];
                        }
        
                        closeEditorPopup = function(name) {
                            if ((typeof popups != "undefined") && popups[name] != undefined && !popups[name].closed) {
                                popups[name].close();
                            }
                        }
                    //]]>
        script, false);
        if ($this->is_enabled()) {
            $js_setup_object = 'wysiwyg' . $this->get_html_id();
            $force_load = '';
            if (!$this->is_hidden()) {
                if ($this->get_force_load()) {
                    $force_load = $js_setup_object . '.setup("exact");';
                } else {
                    $force_load = 'jQuery(window).on("load", ' . $js_setup_object . '.setup.bind(' . $js_setup_object . ', "exact"));';
                }
            }
            $html = $this->_get_buttons_html() . '<textarea name="' . $this->get_name() . '" title="' . $this->get_title() . '" ' . $this->_get_ui_id() . ' id="' . $this->get_html_id() . '"' . ' class="textarea' . $this->get_class() . '" ' . $this->serialize($this->get_html_attributes()) . ' >' . $this->get_escaped_value() . '</textarea>' . $js . $this->get_inline_js($js_setup_object, $force_load);
            $html = $this->_wrap_into_container($html);
            $html .= $this->get_after_element_html();
            return $html;
        } else {
            // Display only buttons to additional features
            if ($this->get_plugin_config_options('magentowidget', 'window_url')) {
                $html = $this->_get_buttons_html() . $js . parent::get_element_html();
                if ($this->get_config('add_widgets')) {
                    $html .= $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], <<<script
                                                //<![CDATA[
                                                require(["jquery", "mage/translate", "mage/adminhtml/wysiwyg/widget"], function(jQuery){
                                                    (function(\$) {
                                                        \$.mage.translate.add({$this->serializer->serialize($this->get_button_translations())})
                                                    })(jQuery);
                                                });
                                                //]]>'
                    script, false);
                }
                $html = $this->_wrap_into_container($html);
                return $html;
            }
            return parent::get_element_html();
        }
    }
    /**
     * Returns theme
     *
     * @return mixed
     */
    public function get_theme()
    {
        if (!$this->has_data('theme')) {
            return 'simple';
        }
        return $this->_get_data('theme');
    }
    /**
     * Return Editor top Buttons HTML
     *
     * @return string
     */
    protected function _get_buttons_html()
    {
        $buttons_html = '<div id="buttons' . $this->get_html_id() . '" class="buttons-set">';
        if ($this->is_enabled()) {
            $buttons_html .= $this->_get_toggle_button_html($this->is_toggle_button_visible());
            $buttons_html .= $this->_get_plugin_buttons_html($this->is_hidden());
        } else {
            $buttons_html .= $this->_get_plugin_buttons_html(true);
        }
        $buttons_html .= '</div>';
        return $buttons_html;
    }
    /**
     * Return HTML button to toggling WYSIWYG
     *
     * @param bool $visible
     * @return string
     */
    protected function _get_toggle_button_html($visible = true)
    {
        $html = $this->_get_button_html(['title' => $this->translate('Show / Hide Editor'), 'class' => 'action-show-hide', 'style' => $visible ? '' : 'display:none', 'id' => 'toggle' . $this->get_html_id()]);
        return $html;
    }
    /**
     * Prepare Html buttons for additional WYSIWYG features
     *
     * @param bool $visible Display button or not
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _get_plugin_buttons_html($visible = true)
    {
        $buttons_html = '';
        // Button to widget insertion window
        if ($this->get_config('add_widgets')) {
            $buttons_html .= $this->_get_button_html(['title' => $this->translate('Insert Widget...'), 'onclick' => "widgetTools.openDialog('" . $this->get_plugin_config_options('magentowidget', 'window_url') . 'widget_target_id/' . $this->get_html_id() . "/')", 'class' => 'action-add-widget plugin', 'style' => $visible ? '' : 'display:none']);
        }
        // Button to media images insertion window
        if ($this->get_config('add_images')) {
            $html_id = $this->get_html_id();
            $url = $this->get_config('files_browser_window_url') . 'target_element_id/' . $html_id . '/' . (null !== $this->get_config('store_id') ? 'store/' . $this->get_config('store_id') . '/"' : '');
            $buttons_html .= $this->_get_button_html(['title' => $this->translate('Insert Image...'), 'onclick' => 'MediabrowserUtility.openDialog(\'' . $url . '\', null, null, null, { \'targetElementId\': \'' . $html_id . '\' })', 'class' => 'action-add-image plugin', 'style' => $visible ? '' : 'display:none']);
        }
        if (is_array($this->get_config('plugins'))) {
            foreach ($this->get_config('plugins') as $plugin) {
                if (isset($plugin['options']) && $this->_check_plugin_button_options($plugin['options'])) {
                    $button_options = $this->_prepare_button_options($plugin['options']);
                    if (!$visible) {
                        $config_style = '';
                        if (isset($button_options['style'])) {
                            $config_style = $button_options['style'];
                        }
                        $button_options['style'] = 'display:none; ' . $config_style;
                    }
                    $buttons_html .= $this->_get_button_html($button_options);
                }
            }
        }
        return $buttons_html;
    }
    /**
     * Prepare button options array to create button html
     *
     * @param array $options
     * @return array
     */
    protected function _prepare_button_options($options)
    {
        $button_options = [];
        $button_options['class'] = 'plugin';
        foreach ($options as $name => $value) {
            $button_options[$name] = $value;
        }
        $button_options = $this->_prepare_options($button_options);
        return $button_options;
    }
    /**
     * Check if plugin button options have required values
     *
     * @param array $pluginOptions
     * @return boolean
     */
    protected function _check_plugin_button_options($plugin_options)
    {
        if (!isset($plugin_options['title'])) {
            return false;
        }
        return true;
    }
    /**
     * Convert options
     *
     * Convert options by replacing template constructions ( like {{var_name}} )
     * with data from this element object
     *
     * @param array $options
     * @return array
     */
    protected function _prepare_options($options)
    {
        $prepared_options = [];
        foreach ($options as $name => $value) {
            if (is_array($value) && isset($value['search']) && isset($value['subject'])) {
                $subject = $value['subject'];
                foreach ($value['search'] as $part) {
                    $subject = str_replace('{{' . $part . '}}', $this->get_data_using_method($part), $subject);
                }
                $prepared_options[$name] = $subject;
            } else {
                $prepared_options[$name] = $value;
            }
        }
        return $prepared_options;
    }
    /**
     * Return custom button HTML
     *
     * @param array $data Button params
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _get_button_html($data)
    {
        $id = empty($data['id']) ? 'buttonId' . $this->random->get_random_string(10) : $data['id'];
        $html = '<button type="button"';
        $html .= ' class="scalable ' . (isset($data['class']) ? $data['class'] : '') . '"';
        $html .= ' id="' . $id . '"';
        $html .= '>';
        $html .= isset($data['title']) ? '<span><span><span>' . $data['title'] . '</span></span></span>' : '';
        $html .= '</button>';
        if (!empty($data['onclick'])) {
            $html .= $this->secure_renderer->render_event_listener_as_tag('onclick', $data['onclick'], "#{$id}");
        }
        if (!empty($data['style'])) {
            $html .= $this->secure_renderer->render_style_as_tag($data['style'], "#{$id}");
        }
        return $html;
    }
    /**
     * Wraps Editor HTML into div if 'use_container' config option is set to true
     *
     * If 'no_display' config option is set to true, the div will be invisible
     *
     * @param string $html HTML code to wrap
     * @return string
     */
    protected function _wrap_into_container($html)
    {
        if (!$this->get_config('use_container')) {
            return '<div class="admin__control-wysiwig">' . $html . '</div>';
        }
        $id = 'editor' . $this->get_html_id();
        $html = '<div id="' . $id . '" ' . ($this->get_config('container_class') ? ' class="admin__control-wysiwig ' . $this->get_config('container_class') . '"' : '') . '>' . $html . '</div>';
        if ($this->get_config('no_display')) {
            $html .= $this->secure_renderer->render_style_as_tag('display: none;', "#{$id}");
        }
        return $html;
    }
    /**
     * Editor config retriever
     *
     * @param string $key Config var key
     * @return mixed
     */
    public function get_config($key = null)
    {
        if (!$this->_get_data('config') instanceof \Magento\Framework\Data_Object) {
            $config = new \Magento\Framework\Data_Object();
            $this->set_config($config);
        }
        if ($key !== null) {
            return $this->_get_data('config')->get_data($key);
        }
        return $this->_get_data('config');
    }
    /**
     * Translate string using defined helper
     *
     * @param string $string String to be translated
     * @return \Magento\Framework\Phrase
     */
    public function translate($string)
    {
        return (string) new \Magento\Framework\Phrase($string);
    }
    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function is_enabled()
    {
        $result = false;
        if ($this->get_config('enabled')) {
            $result = $this->has_data('wysiwyg') ? $result = $this->get_wysiwyg() : true;
        }
        return $result;
    }
    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function is_hidden()
    {
        return $this->get_config('hidden');
    }
    /**
     * Is Toggle Button Visible
     *
     * @return bool
     */
    protected function is_toggle_button_visible()
    {
        return !$this->get_config()->has_data('toggle_button') || $this->get_config('toggle_button');
    }
    /**
     * Returns inline js to initialize wysiwyg adapter
     *
     * @param string $jsSetupObject
     * @param string $forceLoad
     * @return string
     */
    protected function get_inline_js($js_setup_object, $force_load)
    {
        $js_string = '
                //<![CDATA[
                window.tinyMCE_GZ = window.tinyMCE_GZ || {};
                window.tinyMCE_GZ.loaded = true;
                require([
                "jquery",
                "mage/translate",
                "mage/adminhtml/events",
                "mage/adminhtml/wysiwyg/tiny_mce/setup",
                "mage/adminhtml/wysiwyg/widget"
                ], function(jQuery){' . "\n" . '  (function($) {$.mage.translate.add(' . $this->serializer->serialize($this->get_button_translations()) . ')})(jQuery);' . "\n" . $js_setup_object . ' = new wysiwygSetup("' . $this->get_html_id() . '", ' . $this->get_json_config() . ');' . $force_load . '
                    editorFormValidationHandler = ' . $js_setup_object . '.onFormValidation.bind(' . $js_setup_object . ');
                    Event.observe("toggle' . $this->get_html_id() . '", "click", ' . $js_setup_object . '.toggle.bind(' . $js_setup_object . '));
                    varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
                //]]>
                });';
        return $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], $js_string, false);
    }
    /**
     * @inheritdoc
     */
    public function get_html_id()
    {
        $suffix = $this->get_config('dynamic_id') ? '${ $.wysiwygUniqueSuffix }' : '';
        return parent::get_html_id() . $suffix;
    }
}