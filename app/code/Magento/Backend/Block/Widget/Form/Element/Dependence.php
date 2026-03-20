<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Form element dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 *
 * @api
 * @since 100.0.2
 */
class Dependence extends \Magento\Backend\Block\Abstract_Block
{
    /**
     * name => id mapper
     * @var array
     */
    protected $_fields = [];
    /**
     * Dependencies mapper (by names)
     * array(
     *     'dependent_name' => array(
     *         'depends_from_1_name' => 'mixed value',
     *         'depends_from_2_name' => 'some another value',
     *         ...
     *     )
     * )
     * @var array
     */
    protected $_depends = [];
    /**
     * Additional configuration options for the dependencies javascript controller
     *
     * @var array
     */
    protected $_config_options = [];
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $_field_factory;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_json_encoder;
    /**
     * @var SecureHtmlRenderer
     */
    protected $secure_renderer;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Config\Model\Config\Structure\Element\Dependency\Field_Factory $field_factory, array $data = [], ?Secure_Html_Renderer $secure_renderer = null)
    {
        $this->_json_encoder = $json_encoder;
        $this->_field_factory = $field_factory;
        parent::__construct($context, $data);
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
    }
    /**
     * Add name => id mapping
     *
     * @param string $fieldId - element ID in DOM
     * @param string $fieldName - element name in their fieldset/form namespace
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function add_field_map($field_id, $field_name)
    {
        $this->_fields[$field_name] = $field_id;
        return $this;
    }
    /**
     * Register field name dependence one from each other by specified values
     *
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\Field|string $refField
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function add_field_dependence($field_name, $field_name_from, $ref_field)
    {
        if (!is_object($ref_field)) {
            /** @var $refField \Magento\Config\Model\Config\Structure\Element\Dependency\Field */
            $ref_field = $this->_field_factory->create(['fieldData' => ['value' => (string) $ref_field], 'fieldPrefix' => '']);
        }
        $this->_depends[$field_name][$field_name_from] = $ref_field;
        return $this;
    }
    /**
     * Add misc configuration options to the javascript dependencies controller
     *
     * @param array $options
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function add_config_options(array $options)
    {
        $this->_config_options = array_merge($this->_config_options, $options);
        return $this;
    }
    /**
     * HTML output getter
     *
     * @return string
     */
    protected function _to_html()
    {
        if (!$this->_depends) {
            return '';
        }
        $params = $this->_get_depends_json();
        if ($this->_config_options) {
            $params .= ', ' . $this->_json_encoder->encode($this->_config_options);
        }
        $script_string = 'require([\'mage/adminhtml/form\'], function(){
    new FormElementDependenceController(' . $params . ');
});';
        return $this->secure_renderer->render_tag('script', [], $script_string, false);
    }
    /**
     * Field dependencies JSON map generator
     *
     * @return string
     */
    protected function _get_depends_json()
    {
        $result = [];
        foreach ($this->_depends as $to => $row) {
            foreach ($row as $from => $field) {
                /** @var $field \Magento\Config\Model\Config\Structure\Element\Dependency\Field */
                $result[$this->_fields[$to]][$this->_fields[$from]] = ['values' => $field->get_values(), 'negative' => $field->is_negative()];
            }
        }
        return $this->_json_encoder->encode($result);
    }
}