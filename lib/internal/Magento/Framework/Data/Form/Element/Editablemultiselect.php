<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Form editable select element
 *
 * Element allows inline modification of textual data within select
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Form editable multiselect element.
 */
class Editablemultiselect extends \Magento\Framework\Data\Form\Element\Multiselect
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
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, array $data = [], ?\Magento\Framework\Serialize\Serializer\Json $serializer = null, ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $random = $random ?? Object_Manager::get_instance()->get(Random::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer, $random);
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->secure_renderer = $secure_renderer;
        $this->random = $random;
    }
    /**
     * Name of the default JavaScript class that is used to make multiselect editable
     *
     * This class must define init() method and receive configuration in the constructor
     */
    public const DEFAULT_ELEMENT_JS_CLASS = 'EditableMultiselect';
    /**
     * Retrieve HTML markup of the element
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function get_element_html()
    {
        $html = parent::get_element_html();
        $select_config = $this->get_data('select_config');
        if ($this->get_data('disabled')) {
            $select_config['is_entity_editable'] = false;
        }
        $element_js_class = self::DEFAULT_ELEMENT_JS_CLASS;
        if ($this->get_data('element_js_class')) {
            $element_js_class = $this->get_data('element_js_class');
        }
        $select_config_json = $this->serializer->serialize($select_config);
        $js_object_name = $this->get_js_object_name();
        // TODO: TaxRateEditableMultiselect should be moved to a static .js module.
        $html .= $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], <<<script
                        require([
                            'jquery'
                        ], function( \$ ){
        
                            function isResolved(){
                                return typeof window['{$element_js_class}'] !== 'undefined';
                            }
        
                            function init(){
                                var {$js_object_name} = new {$element_js_class}({$select_config_json});
        
                                {$js_object_name}.init();
                            }
        
                            function check( tries, delay ){
                                if( isResolved() ){
                                    init();
                                }
                                else if( tries-- ){
                                    setTimeout( check.bind(this, tries, delay), delay);
                                }
                                else{
                                    console.warn( 'Unable to resolve dependency: {$element_js_class}' );
                                }
                            }
        
                           check(8, 500);
        
                        });
        script, false);
        return $html;
    }
    /**
     * Retrieve HTML markup of given select option
     *
     * @param array $option
     * @param string[] $selected
     *
     * @return string
     */
    protected function _option_to_html($option, $selected)
    {
        $option_id = 'optId' . $this->random->get_random_string(8);
        $html = '<option value="' . $this->_escape($option['value']) . '" id="' . $option_id . '" ';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        if (in_array((string) $option['value'], $selected)) {
            $html .= ' selected="selected"';
        }
        if ($this->get_data('disabled')) {
            // if element is disabled then no data modification is allowed
            $html .= ' disabled="disabled" data-is-removable="no" data-is-editable="no"';
        }
        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        if (!empty($option['style'])) {
            $html .= $this->secure_renderer->render_style_as_tag($option['style'], "#{$option_id}");
        }
        return $html;
    }
}