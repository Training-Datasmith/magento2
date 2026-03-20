<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Abstract_Form;
use Magento\Framework\Data\Form\Element\Renderer\Renderer_Interface;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Data form abstract class
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class Abstract_Element extends Abstract_Form
{
    /**
     * @var string|int
     */
    protected $_id;
    /**
     * @var string
     */
    protected $_type;
    /**
     * @var Form
     */
    protected $_form;
    /**
     * @var array
     */
    protected $_elements;
    /**
     * @var RendererInterface
     */
    protected $_renderer;
    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @var bool
     */
    protected $_advanced = false;
    /**
     * @var Escaper
     */
    protected $_escaper;
    /**
     * @var string
     */
    private $lock_html_attribute = 'data-locked';
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
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        $this->_escaper = $escaper;
        parent::__construct($factory_element, $factory_collection, $data);
        $this->_renderer = \Magento\Framework\Data\Form::get_element_renderer();
        $this->secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $this->random = $random ?? Object_Manager::get_instance()->get(Random::class);
    }
    /**
     * Generate this element's ID.
     *
     * @return string
     */
    private function generate_element_id(): string
    {
        if (!$this->has_data('formelementhookid')) {
            $this->set_data('formelementhookid', 'elemId' . $this->random->get_random_string(10));
        }
        return $this->get_data('formelementhookid');
    }
    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool $after
     * @return Form
     */
    public function add_element(Abstract_Element $element, $after = false)
    {
        if ($this->get_form()) {
            $this->get_form()->check_element_id($element->get_id());
            $this->get_form()->add_element_to_collection($element);
        }
        parent::add_element($element, $after);
        return $this;
    }
    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @return bool
     */
    public function is_advanced()
    {
        return $this->_advanced;
    }
    /**
     * Set _advanced layout property
     *
     * @param bool $advanced
     * @return $this
     */
    public function set_advanced($advanced)
    {
        $this->_advanced = $advanced;
        return $this;
    }
    /**
     * Get id.
     *
     * @return string|int
     */
    public function get_id()
    {
        return $this->_id;
    }
    /**
     * Get type.
     *
     * @return string
     */
    public function get_type()
    {
        return $this->_type;
    }
    /**
     * Get form
     *
     * @return Form
     */
    public function get_form()
    {
        return $this->_form;
    }
    /**
     * Set the Id.
     *
     * @param string|int $id
     * @return $this
     */
    public function set_id($id)
    {
        $this->_id = $id;
        $this->set_data('html_id', $id);
        return $this;
    }
    /**
     * Get the Html Id.
     *
     * @return string
     */
    public function get_html_id()
    {
        return $this->_escaper->escape_html($this->get_form()->get_html_id_prefix() . $this->get_data('html_id') . $this->get_form()->get_html_id_suffix());
    }
    /**
     * Get the name.
     *
     * @return mixed
     */
    public function get_name()
    {
        $name = $this->_escaper->escape_html($this->get_data('name'));
        if ($suffix = $this->get_form()->get_field_name_suffix()) {
            $name = $this->get_form()->add_suffix_to_name($name, $suffix);
        }
        return $name;
    }
    /**
     * Set the type.
     *
     * @param string $type
     * @return $this
     */
    public function set_type($type)
    {
        $this->_type = $type;
        $this->set_data('type', $type);
        return $this;
    }
    /**
     * Set form.
     *
     * @param AbstractForm $form
     * @return $this
     */
    public function set_form($form)
    {
        $this->_form = $form;
        return $this;
    }
    /**
     * Remove field
     *
     * @param string $elementId
     * @return AbstractForm
     */
    public function remove_field($element_id)
    {
        $this->get_form()->remove_field($element_id);
        return parent::remove_field($element_id);
    }
    /**
     * Return the attributes for Html.
     *
     * @return string[]
     */
    public function get_html_attributes()
    {
        return ['type', 'title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'autocomplete', 'tabindex', 'placeholder', 'data-form-part', 'data-role', 'data-action', 'checked'];
    }
    /**
     * Add a class.
     *
     * @param string $class
     * @return $this
     */
    public function add_class($class)
    {
        $old_class = $this->get_class();
        $this->set_class($old_class . ' ' . $class);
        return $this;
    }
    /**
     * Remove CSS class
     *
     * @param string $class
     * @return $this
     */
    public function remove_class($class)
    {
        $classes = array_unique(explode(' ', $this->get_class() ?? ''));
        if (false !== $key = array_search($class, $classes)) {
            unset($classes[$key]);
        }
        $this->set_class(implode(' ', $classes));
        return $this;
    }
    /**
     * Escape a string's contents.
     *
     * @param string $string
     * @return string
     */
    protected function _escape($string)
    {
        return $this->_escaper->escape_html($string);
    }
    /**
     * Return the escaped value of the element specified by the given index.
     *
     * @param null|int|string $index
     * @return string
     */
    public function get_escaped_value($index = null)
    {
        $value = $this->get_value($index);
        if ($filter = $this->get_value_filter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }
    /**
     * Set the renderer.
     *
     * @param RendererInterface $renderer
     * @return $this
     */
    public function set_renderer(Renderer_Interface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }
    /**
     * Get the renderer.
     *
     * @return RendererInterface
     */
    public function get_renderer()
    {
        return $this->_renderer;
    }
    /**
     * Get Ui Id.
     *
     * @param null|string $suffix
     * @return string
     */
    protected function _get_ui_id($suffix = null)
    {
        if ($this->_renderer instanceof \Magento\Framework\View\Element\Abstract_Block) {
            return $this->_renderer->get_ui_id($this->get_type(), $this->get_name(), $suffix);
        } else {
            return ' data-ui-id="form-element-' . $this->_escaper->escape_html($this->get_name()) . ($suffix ?: '') . '"';
        }
    }
    /**
     * Get the Html for the element.
     *
     * @return string
     */
    public function get_element_html()
    {
        $html = '';
        $html_id = $this->get_html_id();
        $before_element_html = $this->get_before_element_html();
        if ($before_element_html) {
            $html .= '<label class="addbefore" for="' . $html_id . '">' . $before_element_html . '</label>';
        }
        if (is_array($this->get_value())) {
            foreach ($this->get_value() as $value) {
                $html .= $this->get_html_for_input_by_value($this->_escape($value));
            }
        } else {
            $html .= $this->get_html_for_input_by_value($this->get_escaped_value());
        }
        $after_element_js = $this->get_after_element_js();
        if ($after_element_js) {
            $html .= $after_element_js;
        }
        $after_element_html = $this->get_after_element_html();
        if ($after_element_html) {
            $html .= '<label class="addafter" for="' . $html_id . '">' . $after_element_html . '</label>';
        }
        return $html;
    }
    /**
     * Get the before element html.
     *
     * @return mixed
     */
    public function get_before_element_html()
    {
        return $this->get_data('before_element_html');
    }
    /**
     * Generate HTML to replace unsecure attributes.
     *
     * @return string
     */
    private function generate_attributes_substitute(): string
    {
        $html = '';
        //Rendering element's style as separate tag.
        if ($this->get_style()) {
            $selector = "*[formelementhookid='{$this->generate_element_id()}']";
            if ($id = $this->get_html_id()) {
                $selector = "#{$id}";
            }
            $html .= $this->secure_renderer->render_style_as_tag($this->get_style(), $selector);
        }
        //Rendering each event listener as a separate script tag.
        $events = array_filter($this->get_html_attributes(), function (string $attribute): bool {
            return mb_strpos($attribute, 'on') === 0;
        });
        foreach ($events as $event) {
            $event_short = mb_substr($event, 2);
            $method_name = 'getOn' . $event_short;
            if ($event_listener = $this->{$method_name}()) {
                $html .= $this->secure_renderer->render_event_listener_as_tag($event, $event_listener, "*[formelementhookid='{$this->generate_element_id()}']");
            }
        }
        return $html;
    }
    /**
     * Get the after element html.
     *
     * @return mixed
     */
    public function get_after_element_html()
    {
        return $this->get_data('after_element_html') . $this->generate_attributes_substitute();
    }
    /**
     * Get the after element Javascript.
     *
     * @return mixed
     */
    public function get_after_element_js()
    {
        return $this->get_data('after_element_js');
    }
    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix
     * @param string $scopeLabel
     * @return string
     */
    public function get_label_html($id_suffix = '', $scope_label = '')
    {
        $scope_label = $scope_label ? ' data-config-scope="' . $scope_label . '"' : '';
        if ($this->get_label() !== null) {
            $html = '<label class="label admin__field-label" for="' . $this->get_html_id() . $id_suffix . '"' . $this->_get_ui_id('label') . '><span' . $scope_label . '>' . $this->_escape($this->get_label()) . '</span></label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }
    /**
     * Get the default html.
     *
     * @return mixed
     */
    public function get_default_html()
    {
        $html = $this->get_data('default_html');
        if ($html === null) {
            $html = $this->get_no_span() === true ? '' : '<div class="admin__field">' . "\n";
            $html .= $this->get_label_html();
            $html .= $this->get_element_html();
            $html .= $this->get_no_span() === true ? '' : '</div>' . "\n";
        }
        return $html;
    }
    /**
     * Get the html.
     *
     * @return mixed
     */
    public function get_html()
    {
        if ($this->get_required()) {
            $this->add_class('required-entry _required');
        }
        if ($this->_renderer) {
            $html = $this->_renderer->render($this);
        } else {
            $html = $this->get_default_html();
        }
        return $html;
    }
    /**
     * Get the html.
     *
     * @return mixed
     */
    public function to_html()
    {
        return $this->get_html();
    }
    /**
     * Serialize the element.
     *
     * @param string[] $attributes
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     */
    public function serialize($attributes = [], $value_separator = '=', $field_separator = ' ', $quote = '"')
    {
        if ($this->is_locked() && !empty($attributes)) {
            $attributes[] = $this->lock_html_attribute;
        }
        if (in_array('disabled', $attributes) && !empty($this->_data['disabled'])) {
            $this->_data['disabled'] = 'disabled';
        } else {
            unset($this->_data['disabled']);
        }
        if (in_array('checked', $attributes) && !empty($this->_data['checked'])) {
            $this->_data['checked'] = 'checked';
        } else {
            unset($this->_data['checked']);
        }
        $attributes[] = 'formelementhookid';
        $this->generate_element_id();
        //Unset attributes that are to be rendered as separate tags
        $attributes = array_filter($attributes, function (string $attribute): bool {
            return $attribute !== 'style' && mb_strpos($attribute, 'on') !== 0;
        });
        return parent::serialize($attributes, $value_separator, $field_separator, $quote);
    }
    /**
     * Indicates the elements readonly status.
     *
     * @return mixed
     */
    public function get_readonly()
    {
        if ($this->has_data('readonly_disabled')) {
            return $this->_get_data('readonly_disabled');
        }
        return $this->_get_data('readonly');
    }
    /**
     * Get the container Id.
     *
     * @return mixed
     */
    public function get_html_container_id()
    {
        if ($this->has_data('container_id')) {
            return $this->get_data('container_id');
        } elseif ($id_prefix = $this->get_form()->get_field_container_id_prefix()) {
            return $id_prefix . $this->get_id();
        }
        return '';
    }
    /**
     * Add specified values to element values
     *
     * @param string|int|array $values
     * @param bool $overwrite
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function add_element_values($values, $overwrite = false)
    {
        if (empty($values) || is_string($values) && trim($values) == '') {
            return $this;
        }
        if (!is_array($values)) {
            $values = $this->_escaper->escape_html(trim($values));
            $values = [$values => $values];
        }
        $element_values = $this->get_values();
        if (!empty($element_values)) {
            foreach ($values as $key => $value) {
                if (isset($element_values[$key]) && $overwrite || !isset($element_values[$key])) {
                    $element_values[$key] = $this->_escaper->escape_html($value);
                }
            }
            $values = $element_values;
        }
        $this->set_values($values);
        return $this;
    }
    /**
     * Lock element
     *
     * @return void
     */
    public function lock()
    {
        $this->set_data($this->lock_html_attribute, 1);
    }
    /**
     * Is element locked
     *
     * @return bool
     */
    public function is_locked()
    {
        return $this->get_data($this->lock_html_attribute) == 1;
    }
    /**
     * Get input html by sting value.
     *
     * @param string|null $value
     *
     * @return string
     */
    private function get_html_for_input_by_value($value)
    {
        return '<input id="' . $this->get_html_id() . '" name="' . $this->get_name() . '" ' . $this->_get_ui_id() . ' value="' . $value . '" ' . $this->serialize($this->get_html_attributes()) . '/>';
    }
}