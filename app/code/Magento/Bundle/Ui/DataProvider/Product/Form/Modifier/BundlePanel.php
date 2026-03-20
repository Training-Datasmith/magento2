<?php

/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Ui\Data_Provider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Catalog\Api\Data\Product_Attribute_Interface;
use Magento\Catalog\Model\Config\Source\Product_Price_Options_Interface;
use Magento\Catalog\Model\Locator\Locator_Interface;
use Magento\Catalog\Ui\Data_Provider\Product\Form\Modifier\Abstract_Modifier;
use Magento\Framework\Stdlib\Array_Manager;
use Magento\Framework\Url_Interface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
/**
 * Create Ship Bundle Items and Affect Bundle Product Selections fields
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle_Panel extends Abstract_Modifier
{
    public const GROUP_CONTENT = 'content';
    public const CODE_SHIPMENT_TYPE = 'shipment_type';
    public const CODE_BUNDLE_DATA = 'bundle-items';
    public const CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS = 'affect_bundle_product_selections';
    public const CODE_BUNDLE_HEADER = 'bundle_header';
    public const CODE_BUNDLE_OPTIONS = 'bundle_options';
    public const SORT_ORDER = 20;
    /**
     * @var UrlInterface
     */
    protected $url_builder;
    /**
     * @var ShipmentType
     */
    protected $shipment_type;
    /**
     * @var ArrayManager
     */
    protected $array_manager;
    /**
     * @var LocatorInterface
     */
    protected $locator;
    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ShipmentType $shipmentType
     * @param ArrayManager $arrayManager
     */
    public function __construct(Locator_Interface $locator, Url_Interface $url_builder, Shipment_Type $shipment_type, Array_Manager $array_manager)
    {
        $this->locator = $locator;
        $this->url_builder = $url_builder;
        $this->shipment_type = $shipment_type;
        $this->array_manager = $array_manager;
    }
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modify_meta(array $meta)
    {
        $meta = $this->remove_fixed_tier_price($meta);
        $group_code = static::CODE_BUNDLE_DATA;
        $path = $this->array_manager->find_path($group_code, $meta, null, 'children');
        if (empty($path)) {
            $meta[$group_code]['children'] = [];
            $meta[$group_code]['arguments']['data']['config'] = ['componentType' => Fieldset::NAME, 'label' => __('Bundle Items'), 'collapsible' => true];
            $path = $this->array_manager->find_path($group_code, $meta, null, 'children');
        }
        $meta = $this->array_manager->merge($path, $meta, ['arguments' => ['data' => ['config' => ['dataScope' => '', 'opened' => true, 'sortOrder' => $this->get_next_group_sort_order($meta, static::GROUP_CONTENT, static::SORT_ORDER)]]], 'children' => ['modal' => ['arguments' => ['data' => ['config' => ['isTemplate' => false, 'componentType' => Modal::NAME, 'dataScope' => '', 'provider' => 'product_form.product_form_data_source', 'options' => ['title' => __('Add Products to Option'), 'buttons' => [['text' => __('Cancel'), 'actions' => ['closeModal']], ['text' => __('Add Selected Products'), 'class' => 'action-primary', 'actions' => [['targetName' => 'index = bundle_product_listing', 'actionName' => 'save'], 'closeModal']]]]]]], 'children' => ['bundle_product_listing' => ['arguments' => ['data' => ['config' => ['autoRender' => false, 'componentType' => 'insertListing', 'dataScope' => 'bundle_product_listing', 'externalProvider' => 'bundle_product_listing.bundle_product_listing_data_source', 'selectionsProvider' => 'bundle_product_listing.bundle_product_listing.product_columns.ids', 'ns' => 'bundle_product_listing', 'render_url' => $this->url_builder->get_url('mui/index/render'), 'realTimeLink' => false, 'dataLinks' => ['imports' => false, 'exports' => true], 'behaviourType' => 'simple', 'externalFilterMode' => true]]]]]], self::CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS => ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'dataScope' => 'data.affect_bundle_product_selections', 'visible' => false, 'value' => '1']]]], self::CODE_BUNDLE_HEADER => $this->get_bundle_header(), self::CODE_BUNDLE_OPTIONS => $this->get_bundle_options()]]);
        //TODO: Remove this workaround after MAGETWO-49902 is fixed
        $bundle_items_group = $this->array_manager->get($path, $meta);
        $meta = $this->array_manager->remove($path, $meta);
        $meta = $this->array_manager->set($path, $meta, $bundle_items_group);
        $meta = $this->modify_shipment_type($meta);
        return $meta;
    }
    /**
     * Remove option with fixed tier price from config.
     *
     * @param array $meta
     * @return array
     */
    private function remove_fixed_tier_price(array $meta)
    {
        $tier_price_path = $this->array_manager->find_path(Product_Attribute_Interface::CODE_TIER_PRICE, $meta, null, 'children');
        $price_path = $this->array_manager->find_path(Product_Attribute_Interface::CODE_TIER_PRICE_FIELD_PRICE, $meta, $tier_price_path);
        $price_path = $this->array_manager->slice_path($price_path, 0, -1) . '/value_type/arguments/data/options';
        $price = $this->array_manager->get($price_path, $meta);
        if ($price) {
            $meta = $this->array_manager->remove($price_path, $meta);
            foreach ($price as $key => $item) {
                if ($item['value'] == Product_Price_Options_Interface::VALUE_FIXED) {
                    unset($price[$key]);
                }
            }
            $meta = $this->array_manager->merge($this->array_manager->slice_path($price_path, 0, -1), $meta, ['options' => $price]);
        }
        return $meta;
    }
    /**
     * @inheritdoc
     */
    public function modify_data(array $data)
    {
        return $data;
    }
    /**
     * Modify Shipment Type configuration
     *
     * @param array $meta
     * @return array
     */
    private function modify_shipment_type(array $meta)
    {
        $actual_path = $this->array_manager->find_path(static::CODE_SHIPMENT_TYPE, $meta, null, 'children');
        $meta = $this->array_manager->merge($actual_path . static::META_CONFIG_PATH, $meta, ['dataScope' => stripos($actual_path, self::CODE_BUNDLE_DATA) === 0 ? 'data.product.shipment_type' : 'shipment_type', 'validation' => ['required-entry' => false]]);
        return $meta;
    }
    /**
     * Get bundle header structure
     *
     * @return array
     */
    protected function get_bundle_header()
    {
        return ['arguments' => ['data' => ['config' => ['label' => null, 'formElement' => Container::NAME, 'componentType' => Container::NAME, 'template' => 'ui/form/components/complex', 'sortOrder' => 10]]], 'children' => ['add_button' => ['arguments' => ['data' => ['config' => ['title' => __('Add Option'), 'formElement' => Container::NAME, 'componentType' => Container::NAME, 'component' => 'Magento_Ui/js/form/components/button', 'sortOrder' => 20, 'actions' => [['targetName' => 'product_form.product_form.' . self::CODE_BUNDLE_DATA . '.' . self::CODE_BUNDLE_OPTIONS, 'actionName' => 'processingAddChild']]]]]]]];
    }
    /**
     * Get Bundle Options structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function get_bundle_options()
    {
        return ['arguments' => ['data' => ['config' => ['componentType' => Container::NAME, 'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows', 'template' => 'ui/dynamic-rows/templates/collapsible', 'additionalClasses' => 'admin__field-wide', 'dataScope' => 'data.bundle_options', 'isDefaultFieldScope' => 'is_default', 'bundleSelectionsName' => 'product_bundle_container.bundle_selections']]], 'children' => ['record' => ['arguments' => ['data' => ['config' => ['componentType' => Container::NAME, 'isTemplate' => true, 'is_collection' => true, 'headerLabel' => __('New Option'), 'component' => 'Magento_Ui/js/dynamic-rows/record', 'positionProvider' => 'product_bundle_container.position', 'imports' => ['label' => '${ $.name }' . '.product_bundle_container.option_info.title:value', '__disableTmpl' => ['label' => false]]]]], 'children' => ['product_bundle_container' => ['arguments' => ['data' => ['config' => ['componentType' => 'fieldset', 'collapsible' => true, 'label' => '', 'opened' => true]]], 'children' => ['option_info' => $this->get_option_info(), 'position' => $this->get_hidden_column('position', 20), 'option_id' => $this->get_hidden_column('option_id', 30), 'delete' => $this->get_hidden_column('delete', 40), 'bundle_selections' => ['arguments' => ['data' => ['config' => ['componentType' => Container::NAME, 'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows-grid', 'sortOrder' => 50, 'additionalClasses' => 'admin__field-wide', 'template' => 'Magento_Catalog/components/dynamic-rows-per-page', 'sizesConfig' => ['enabled' => true], 'provider' => 'product_form.product_form_data_source', 'dataProvider' => '${ $.dataScope }' . '.bundle_button_proxy', '__disableTmpl' => ['dataProvider' => false], 'identificationDRProperty' => 'product_id', 'identificationProperty' => 'product_id', 'map' => ['product_id' => 'entity_id', 'name' => 'name', 'sku' => 'sku', 'price' => 'price', 'delete' => '', 'selection_can_change_qty' => '', 'selection_id' => '', 'selection_price_type' => '', 'selection_price_value' => '', 'selection_qty' => '', 'selection_qty_is_integer' => 'selection_qty_is_integer'], 'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }', '__disableTmpl' => ['insertData' => false]], 'imports' => ['inputType' => '${$.provider}:${$.dataScope}.type', '__disableTmpl' => ['inputType' => false]], 'source' => 'product']]], 'children' => ['record' => $this->get_bundle_selections()]], 'modal_set' => $this->get_modal_set()]]]]]];
    }
    /**
     * Prepares configuration for the hidden columns
     *
     * @param string $columnName
     * @param int $sortOrder
     * @return array
     */
    protected function get_hidden_column($column_name, $sort_order)
    {
        return ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'dataScope' => $column_name, 'visible' => false, 'additionalClasses' => ['_hidden' => true], 'sortOrder' => $sort_order]]]];
    }
    /**
     * Get configuration for the modal set: modal and trigger button
     *
     * @return array
     */
    protected function get_modal_set()
    {
        return ['arguments' => ['data' => ['config' => ['sortOrder' => 60, 'formElement' => 'container', 'componentType' => 'container', 'dataScope' => 'bundle_button_proxy', 'component' => 'Magento_Catalog/js/bundle-proxy-button', 'provider' => 'product_form.product_form_data_source', 'listingDataProvider' => 'bundle_product_listing', 'actions' => [['targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA . '.modal', 'actionName' => 'toggleModal'], ['targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA . '.modal.bundle_product_listing', 'actionName' => 'render']], 'title' => __('Add Products to Option')]]]];
    }
    /**
     * Get configuration for option title
     *
     * @return array
     */
    protected function get_title_configuration()
    {
        $result['title']['arguments']['data']['config'] = ['dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'componentType' => Form\Field::NAME, 'dataScope' => $this->is_default_store() ? 'title' : 'default_title', 'label' => $this->is_default_store() ? __('Option Title') : __('Default Title'), 'sortOrder' => 10, 'validation' => ['required-entry' => true]];
        if (!$this->is_default_store()) {
            $result['store_title']['arguments']['data']['config'] = ['dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'componentType' => Form\Field::NAME, 'dataScope' => 'title', 'label' => __('Store View Title'), 'sortOrder' => 15, 'validation' => ['required-entry' => true]];
        }
        return $result;
    }
    /**
     * Get option info
     *
     * @return array
     */
    protected function get_option_info()
    {
        $result = ['arguments' => ['data' => ['config' => ['formElement' => 'container', 'componentType' => Container::NAME, 'component' => 'Magento_Ui/js/form/components/group', 'showLabel' => false, 'additionalClasses' => 'admin__field-group-columns admin__control-group-equal', 'breakLine' => false, 'sortOrder' => 10]]], 'children' => ['type' => ['arguments' => ['data' => ['config' => ['dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Select::NAME, 'componentType' => Form\Field::NAME, 'component' => 'Magento_Ui/js/form/element/select', 'parentContainer' => 'product_bundle_container', 'selections' => 'bundle_selections', 'isDefaultIndex' => 'is_default', 'userDefinedIndex' => 'selection_can_change_qty', 'dataScope' => 'type', 'label' => __('Input Type'), 'sortOrder' => 20, 'options' => [['label' => __('Drop-down'), 'value' => 'select'], ['label' => __('Radio Buttons'), 'value' => 'radio'], ['label' => __('Checkbox'), 'value' => 'checkbox'], ['label' => __('Multiple Select'), 'value' => 'multi']], 'typeMap' => ['select' => 'radio', 'radio' => 'radio', 'checkbox' => 'checkbox', 'multi' => 'checkbox']]]]], 'required' => ['arguments' => ['data' => ['config' => ['dataType' => Form\Element\Data_Type\Number::NAME, 'formElement' => Form\Element\Checkbox::NAME, 'componentType' => Form\Field::NAME, 'description' => __('Required'), 'dataScope' => 'required', 'label' => ' ', 'value' => '1', 'valueMap' => ['true' => '1', 'false' => '0'], 'sortOrder' => 30]]]]]];
        return $this->array_manager->merge('children', $result, $this->get_title_configuration());
    }
    /**
     * Get bundle selections structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function get_bundle_selections()
    {
        return ['arguments' => ['data' => ['config' => ['componentType' => Container::NAME, 'isTemplate' => true, 'component' => 'Magento_Ui/js/dynamic-rows/record', 'is_collection' => true, 'imports' => ['inputType' => '${$.parentName}:inputType', '__disableTmpl' => ['inputType' => false]], 'exports' => ['isDefaultValue' => '${$.parentName}:isDefaultValue.${$.index}', '__disableTmpl' => ['isDefaultValue' => false]]]]], 'children' => ['selection_id' => $this->get_hidden_column('selection_id', 10), 'option_id' => $this->get_hidden_column('option_id', 20), 'product_id' => $this->get_hidden_column('product_id', 30), 'delete' => $this->get_hidden_column('delete', 40), 'is_default' => ['arguments' => ['data' => ['config' => ['formElement' => Form\Element\Checkbox::NAME, 'componentType' => Form\Field::NAME, 'component' => 'Magento_Bundle/js/components/bundle-checkbox', 'parentContainer' => 'product_bundle_container', 'parentSelections' => 'bundle_selections', 'changer' => 'option_info.type', 'dataType' => Form\Element\Data_Type\Boolean::NAME, 'label' => __('Is Default'), 'dataScope' => 'is_default', 'prefer' => 'radio', 'value' => '0', 'sortOrder' => 50, 'valueMap' => ['false' => '0', 'true' => '1']]]]], 'name' => ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'elementTmpl' => 'ui/dynamic-rows/cells/text', 'label' => __('Name'), 'dataScope' => 'name', 'sortOrder' => 60]]]], 'sku' => ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Text::NAME, 'formElement' => Form\Element\Input::NAME, 'elementTmpl' => 'ui/dynamic-rows/cells/text', 'label' => __('SKU'), 'dataScope' => 'sku', 'sortOrder' => 70]]]], 'selection_price_value' => $this->get_selection_price_value(), 'selection_price_type' => $this->get_selection_price_type(), 'selection_qty' => ['arguments' => ['data' => ['config' => ['component' => 'Magento_Bundle/js/components/bundle-option-qty', 'formElement' => Form\Element\Input::NAME, 'componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Number::NAME, 'label' => __('Default Quantity'), 'dataScope' => 'selection_qty', 'value' => '1', 'sortOrder' => 100, 'validation' => ['required-entry' => true, 'validate-number' => true, 'validate-greater-than-zero' => true], 'imports' => ['isInteger' => '${ $.provider }:${ $.parentScope }.selection_qty_is_integer', '__disableTmpl' => ['isInteger' => false]]]]]], 'selection_can_change_qty' => ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'formElement' => Form\Element\Checkbox::NAME, 'dataType' => Form\Element\Data_Type\Price::NAME, 'component' => 'Magento_Bundle/js/components/bundle-user-defined-checkbox', 'label' => __('User Defined'), 'dataScope' => 'selection_can_change_qty', 'value' => '1', 'valueMap' => ['true' => '1', 'false' => '0'], 'sortOrder' => 110, 'imports' => ['inputType' => '${$.parentName}:inputType', '__disableTmpl' => ['inputType' => false]]]]]], 'position' => $this->get_hidden_column('position', 120), 'action_delete' => ['arguments' => ['data' => ['config' => ['componentType' => 'actionDelete', 'dataType' => Form\Element\Data_Type\Text::NAME, 'label' => '', 'fit' => true, 'sortOrder' => 130]]]]]];
    }
    /**
     * Get selection price value structure
     *
     * @return array
     */
    protected function get_selection_price_value()
    {
        return ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Price::NAME, 'formElement' => Form\Element\Input::NAME, 'label' => __('Price'), 'dataScope' => 'selection_price_value', 'value' => '0.00', 'imports' => ['visible' => '!ns = ${ $.ns }, index = ' . Bundle_Price::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['visible' => false]], 'sortOrder' => 80]]]];
    }
    /**
     * Get selection price type structure
     *
     * @return array
     */
    protected function get_selection_price_type()
    {
        return ['arguments' => ['data' => ['config' => ['componentType' => Form\Field::NAME, 'dataType' => Form\Element\Data_Type\Boolean::NAME, 'formElement' => Form\Element\Select::NAME, 'label' => __('Price Type'), 'dataScope' => 'selection_price_type', 'value' => '0', 'options' => [['label' => __('Fixed'), 'value' => '0'], ['label' => __('Percent'), 'value' => '1']], 'imports' => ['visible' => '!ns = ${ $.ns }, index = ' . Bundle_Price::CODE_PRICE_TYPE . ':checked', '__disableTmpl' => ['visible' => false]], 'sortOrder' => 90]]]];
    }
    /**
     * Check that store is default
     *
     * @return bool
     */
    protected function is_default_store()
    {
        return $this->locator->get_product()->get_store_id() == Store::DEFAULT_STORE_ID;
    }
}