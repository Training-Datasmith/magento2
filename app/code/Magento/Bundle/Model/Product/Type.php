<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Resource_Model\Option\Are_Bundle_Options_Salable;
use Magento\Bundle\Model\Resource_Model\Option\Collection;
use Magento\Bundle\Model\Resource_Model\Selection\Collection as Selections;
use Magento\Bundle\Model\Resource_Model\Selection\Collection\Filter_Applier as SelectionCollectionFilterApplier;
use Magento\Catalog\Api\Product_Repository_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\File\Uploader_Factory;
use Magento\Framework\Pricing\Price_Currency_Interface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Array_Utils;
/**
 * Bundle Type Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Type extends \Magento\Catalog\Model\Product\Type\Abstract_Type
{
    /**
     * Product type
     */
    public const TYPE_CODE = 'bundle';
    /**
     * Product is composite
     *
     * @var bool
     */
    protected $_is_composite = true;
    /**
     * Cache key for Options Collection
     *
     * @var string
     */
    protected $_key_options_collection = '_cache_instance_options_collection';
    /**
     * Cache key for Selections Collection
     *
     * @var string
     * @deprecated 100.2.0
     * @see MAGETWO-71174
     */
    protected $_key_selections_collection = '_cache_instance_selections_collection';
    /**
     * Cache key for used Selections
     *
     * @var string
     */
    protected $_key_used_selections = '_cache_instance_used_selections';
    /**
     * Cache key for used selections ids
     *
     * @var string
     */
    protected $_key_used_selections_ids = '_cache_instance_used_selections_ids';
    /**
     * Cache key for used options
     *
     * @var string
     */
    protected $_key_used_options = '_cache_instance_used_options';
    /**
     * Cache key for used options ids
     *
     * @var string
     */
    protected $_key_used_options_ids = '_cache_instance_used_options_ids';
    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_can_configure = true;
    /**
     * Catalog data helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalog_data = null;
    /**
     * Catalog product helper
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalog_product = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store_manager;
    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $_bundle_option;
    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    protected $_bundle_selection;
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_config;
    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory
     */
    protected $_bundle_collection;
    /**
     * @var \Magento\Bundle\Model\ResourceModel\BundleFactory
     */
    protected $_bundle_factory;
    /**
     * @var \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     */
    protected $_bundle_model_selection;
    /**
     * @var PriceCurrencyInterface
     */
    protected $price_currency;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stock_registry;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stock_state;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var SelectionCollectionFilterApplier
     */
    private $selection_collection_filter_applier;
    /**
     * @var ArrayUtils
     */
    private $array_utility;
    /**
     * @var AreBundleOptionsSalable
     */
    private $are_bundle_options_salable;
    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     * @param \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
     * @param \Magento\Bundle\Model\OptionFactory $bundleOption
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param Json|null $serializer
     * @param MetadataPool|null $metadataPool
     * @param SelectionCollectionFilterApplier|null $selectionCollectionFilterApplier
     * @param ArrayUtils|null $arrayUtility
     * @param UploaderFactory|null $uploaderFactory
     * @param AreBundleOptionsSalable|null $areBundleOptionsSalable
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Catalog\Model\Product\Option $catalog_product_option, \Magento\Eav\Model\Config $eav_config, \Magento\Catalog\Model\Product\Type $catalog_product_type, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Media_Storage\Helper\File\Storage\Database $file_storage_db, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Registry $core_registry, \Psr\Log\Logger_Interface $logger, Product_Repository_Interface $product_repository, \Magento\Catalog\Helper\Product $catalog_product, \Magento\Catalog\Helper\Data $catalog_data, \Magento\Bundle\Model\Selection_Factory $bundle_model_selection, \Magento\Bundle\Model\Resource_Model\Bundle_Factory $bundle_factory, \Magento\Bundle\Model\Resource_Model\Selection\Collection_Factory $bundle_collection, \Magento\Catalog\Model\Config $config, \Magento\Bundle\Model\Resource_Model\Selection $bundle_selection, \Magento\Bundle\Model\Option_Factory $bundle_option, \Magento\Store\Model\Store_Manager_Interface $store_manager, Price_Currency_Interface $price_currency, \Magento\Catalog_Inventory\Api\Stock_Registry_Interface $stock_registry, \Magento\Catalog_Inventory\Api\Stock_State_Interface $stock_state, ?Json $serializer = null, ?Metadata_Pool $metadata_pool = null, ?Selection_Collection_Filter_Applier $selection_collection_filter_applier = null, ?Array_Utils $array_utility = null, ?Uploader_Factory $uploader_factory = null, ?Are_Bundle_Options_Salable $are_bundle_options_salable = null)
    {
        $this->_catalog_product = $catalog_product;
        $this->_catalog_data = $catalog_data;
        $this->_store_manager = $store_manager;
        $this->_bundle_option = $bundle_option;
        $this->_bundle_selection = $bundle_selection;
        $this->_config = $config;
        $this->_bundle_collection = $bundle_collection;
        $this->_bundle_factory = $bundle_factory;
        $this->_bundle_model_selection = $bundle_model_selection;
        $this->price_currency = $price_currency;
        $this->_stock_registry = $stock_registry;
        $this->_stock_state = $stock_state;
        $this->metadata_pool = $metadata_pool ?: Object_Manager::get_instance()->get(Metadata_Pool::class);
        $this->selection_collection_filter_applier = $selection_collection_filter_applier ?: Object_Manager::get_instance()->get(Selection_Collection_Filter_Applier::class);
        $this->array_utility = $array_utility ?: Object_Manager::get_instance()->get(Array_Utils::class);
        $this->are_bundle_options_salable = $are_bundle_options_salable ?? Object_Manager::get_instance()->get(Are_Bundle_Options_Salable::class);
        parent::__construct($catalog_product_option, $eav_config, $catalog_product_type, $event_manager, $file_storage_db, $filesystem, $core_registry, $logger, $product_repository, $serializer, $uploader_factory);
    }
    /**
     * Return relation info about used products
     *
     * @return \Magento\Framework\DataObject Object with information data
     */
    public function get_relation_info()
    {
        $info = new \Magento\Framework\Data_Object();
        $info->set_table('catalog_product_bundle_selection')->set_parent_field_name('parent_product_id')->set_child_field_name('product_id');
        return $info;
    }
    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function get_children_ids($parent_id, $required = true)
    {
        return $this->_bundle_selection->get_children_ids($parent_id, $required);
    }
    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @return array
     */
    public function get_parent_ids_by_child($child_id)
    {
        return $this->_bundle_selection->get_parent_ids_by_child($child_id);
    }
    /**
     * Return product sku based on sku_type attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function get_sku($product)
    {
        $sku = parent::get_sku($product);
        if ($product->get_data('sku_type')) {
            return $sku;
        } else {
            $sku_parts = [$sku];
            if ($product->has_custom_options()) {
                $custom_option = $product->get_custom_option('bundle_selection_ids');
                $selection_ids = $this->serializer->unserialize($custom_option->get_value());
                if (!empty($selection_ids)) {
                    $selections = $this->get_selections_by_ids($selection_ids, $product);
                    foreach ($selection_ids as $selection_id) {
                        $entity = $selections->get_item_by_column_value('selection_id', $selection_id);
                        if (isset($entity) && $entity->get_entity_id()) {
                            $sku_parts[] = $entity->get_sku();
                        }
                    }
                }
            }
            return implode('-', $sku_parts);
        }
    }
    /**
     * Return product weight based on weight_type attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function get_weight($product)
    {
        if ($product->get_data('weight_type')) {
            return $product->get_data('weight');
        } else {
            $weight = 0;
            if ($product->has_custom_options()) {
                $custom_option = $product->get_custom_option('bundle_selection_ids');
                $selection_ids = $this->serializer->unserialize($custom_option->get_value());
                $selections = $this->get_selections_by_ids($selection_ids, $product);
                foreach ($selections->get_items() as $selection) {
                    $qty_option = $product->get_custom_option('selection_qty_' . $selection->get_selection_id());
                    if ($qty_option) {
                        $weight += $selection->get_weight() * $qty_option->get_value();
                    } else {
                        $weight += $selection->get_weight();
                    }
                }
            }
            return $weight;
        }
    }
    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function is_virtual($product)
    {
        if ($product->has_custom_options()) {
            $custom_option = $product->get_custom_option('bundle_selection_ids');
            $selection_ids = $this->serializer->unserialize($custom_option->get_value());
            $selections = $this->get_selections_by_ids($selection_ids, $product);
            $virtual_count = 0;
            foreach ($selections->get_items() as $selection) {
                if ($selection->is_virtual()) {
                    $virtual_count++;
                }
            }
            return $virtual_count === count($selections);
        }
        return false;
    }
    /**
     * Before save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function before_save($product)
    {
        parent::before_save($product);
        // If bundle product has dynamic weight, than delete weight attribute
        if (!$product->get_data('weight_type') && $product->has_data('weight')) {
            $product->set_data('weight', false);
        }
        if ($product->get_price_type() == Price::PRICE_TYPE_DYNAMIC) {
            /** unset product custom options for dynamic price */
            if ($product->has_data('product_options')) {
                $product->unset_data('product_options');
            }
        }
        $product->can_affect_options(false);
        if ($product->get_can_save_bundle_selections()) {
            $product->can_affect_options(true);
            $selections = $product->get_bundle_selections_data();
            if (!empty($selections) && $options = $product->get_bundle_options_data()) {
                foreach ($options as $option) {
                    if (empty($option['delete']) || 1 != (int) $option['delete']) {
                        $product->set_type_has_options(true);
                        if (1 == (int) $option['required']) {
                            $product->set_type_has_required_options(true);
                            break;
                        }
                    }
                }
            }
        }
    }
    /**
     * Retrieve bundle options items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\DataObject[]
     */
    public function get_options($product)
    {
        return $this->get_options_collection($product)->get_items();
    }
    /**
     * Retrieve bundle options ids
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function get_options_ids($product)
    {
        return $this->get_options_collection($product)->get_all_ids();
    }
    /**
     * Retrieve bundle option collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function get_options_collection($product)
    {
        if (!$product->has_data($this->_key_options_collection)) {
            /** @var Collection $optionsCollection */
            $options_collection = $this->_bundle_option->create()->get_resource_collection();
            $options_collection->set_product_id_filter($product->get_entity_id());
            $this->set_store_filter($product->get_store_id(), $product);
            $options_collection->set_position_order();
            $store_id = $this->get_store_filter($product);
            if ($store_id instanceof \Magento\Store\Model\Store) {
                $store_id = $store_id->get_id();
            }
            $options_collection->join_values($store_id);
            $product->set_data($this->_key_options_collection, $options_collection);
        }
        return $product->get_data($this->_key_options_collection);
    }
    /**
     * Retrieve bundle selections collection based on used options
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function get_selections_collection($option_ids, $product)
    {
        $store_id = $product->get_store_id();
        $metadata = $this->metadata_pool->get_metadata(\Magento\Catalog\Api\Data\Product_Interface::class);
        /** @var Selections $selectionsCollection */
        $selections_collection = $this->_bundle_collection->create();
        $selections_collection->add_attribute_to_select($this->_config->get_product_attributes())->add_attribute_to_select('tax_class_id')->set_flag('product_children', true)->set_position_order()->add_store_filter($this->get_store_filter($product))->set_store_id($store_id)->add_filter_by_required_options()->set_option_ids_filter($option_ids);
        $this->selection_collection_filter_applier->apply($selections_collection, 'parent_product_id', $product->get_data($metadata->get_link_field()));
        if (!$this->_catalog_data->is_price_global() && $store_id) {
            $website_id = $this->_store_manager->get_store($store_id)->get_website_id();
            $selections_collection->join_prices($website_id);
        }
        return $selections_collection;
    }
    /**
     * Method is needed for specific actions to change given quote options values
     * according current product type logic
     * Example: the catalog inventory validation of decimal qty can change qty to int,
     * so need to change quote item qty option value too.
     *
     * @param  array $options
     * @param  \Magento\Framework\DataObject $option
     * @param  mixed $value
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function update_qty_option($options, \Magento\Framework\Data_Object $option, $value, $product)
    {
        $option_product = $option->get_product($product);
        $option_update_flag = $option->get_has_qty_option_update();
        $option_collection = $this->get_options_collection($product);
        $selections = $this->get_selections_collection($option_collection->get_all_ids(), $product);
        foreach ($selections as $selection) {
            if ($selection->get_product_id() == $option_product->get_id()) {
                foreach ($options as $quote_item_option) {
                    if ($quote_item_option->get_code() == 'selection_qty_' . $selection->get_selection_id()) {
                        if ($option_update_flag) {
                            $quote_item_option->set_value((int) $quote_item_option->get_value());
                        } else {
                            $quote_item_option->set_value($value);
                        }
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepare_quote_item_qty($qty, $product)
    {
        return (int) $qty;
    }
    /**
     * Checking if we can sale this bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function is_salable($product)
    {
        if (!parent::is_salable($product)) {
            return false;
        }
        if ($product->has_data('all_items_salable')) {
            return $product->get_data('all_items_salable');
        }
        $store = $this->_store_manager->get_store();
        $is_salable = $this->are_bundle_options_salable->execute((int) $product->get_entity_id(), (int) $store->get_id());
        $product->set_data('all_items_salable', $is_salable);
        return $is_salable;
    }
    /**
     * Prepare product and its configuration to be added to some products list.
     *
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepare_product(\Magento\Framework\Data_Object $buy_request, $product, $process_mode)
    {
        $result = parent::_prepare_product($buy_request, $product, $process_mode);
        try {
            if (is_string($result)) {
                throw new \Magento\Framework\Exception\Localized_Exception(__($result));
            }
            $selections = [];
            $is_strict_process_mode = $this->_is_strict_process_mode($process_mode);
            $skip_saleable_check = $this->_catalog_product->get_skip_saleable_check();
            $_append_all_selections = (bool) $product->get_skip_check_required_option() || $skip_saleable_check;
            if ($buy_request->get_bundle_options_data()) {
                $options = $this->get_prepared_options($buy_request->get_bundle_options_data());
            } else {
                $options = $buy_request->get_bundle_option();
            }
            if (is_array($options)) {
                $options = $this->recursive_intval($options);
                $option_ids = array_keys($options);
                if (empty($option_ids) && $is_strict_process_mode) {
                    throw new \Magento\Framework\Exception\Localized_Exception(__('Please specify product option(s).'));
                }
                $product->get_type_instance()->set_store_filter($product->get_store_id(), $product);
                $options_collection = $this->get_options_collection($product);
                $this->check_is_all_required_options($product, $is_strict_process_mode, $options_collection, $options);
                $this->validate_radio_and_select_options($options_collection, $options);
                $selection_ids = array_values($this->array_utility->flatten($options));
                // If product has not been configured yet then $selections array should be empty
                if (!empty($selection_ids)) {
                    $selections = $this->get_selections_by_ids($selection_ids, $product);
                    if (count($selections->get_items()) !== count($selection_ids)) {
                        throw new \Magento\Framework\Exception\Localized_Exception(__('The options you selected are not available.'));
                    }
                    // Check if added selections are still on sale
                    $this->check_selections_is_sale($selections, $skip_saleable_check, $options_collection, $options);
                    $options_collection->append_selections($selections, true, $_append_all_selections);
                    $selections = $selections->get_items();
                } else {
                    $selections = [];
                }
            } else {
                $product->set_options_validation_fail(true);
                $product->get_type_instance()->set_store_filter($product->get_store_id(), $product);
                $option_collection = $product->get_type_instance()->get_options_collection($product);
                $option_ids = $product->get_type_instance()->get_options_ids($product);
                $selection_collection = $product->get_type_instance()->get_selections_collection($option_ids, $product);
                $options = $option_collection->append_selections($selection_collection, true, $_append_all_selections);
                $selections = $this->merge_selections_with_options($options, $selections);
            }
            if (is_array($selections) && count($selections) > 0 || !$is_strict_process_mode) {
                $unique_key = [$product->get_id()];
                $selection_ids = [];
                if ($buy_request->get_bundle_options_data()) {
                    $qtys = $buy_request->get_bundle_options_data();
                } else {
                    $qtys = $buy_request->get_bundle_option_qty();
                }
                // Shuffle selection array by option position
                usort($selections, [$this, 'shakeSelections']);
                foreach ($selections as $selection) {
                    $selection_option_id = $selection->get_option_id();
                    $qty = $this->get_qty($selection, $qtys, $selection_option_id);
                    $selection_id = $selection->get_selection_id();
                    $product->add_custom_option('selection_qty_' . $selection_id, $qty, $selection);
                    $selection->add_custom_option('selection_id', $selection_id);
                    $before_qty = $this->get_before_qty($product, $selection);
                    $product->add_custom_option('product_qty_' . $selection->get_id(), $qty + $before_qty, $selection);
                    /*
                     * Create extra attributes that will be converted to product options in order item
                     * for selection (not for all bundle)
                     */
                    $price = $product->get_price_model()->get_selection_final_total_price($product, $selection, 0, 1);
                    $attributes = ['price' => $price, 'qty' => $qty, 'option_label' => $selection->get_option()->get_title(), 'option_id' => $selection->get_option()->get_id()];
                    $_result = $selection->get_type_instance()->prepare_for_cart($buy_request, $selection);
                    $this->check_is_result($_result);
                    $result[] = $_result[0]->set_parent_product_id($product->get_id())->add_custom_option('bundle_option_ids', $this->serializer->serialize(array_map('intval', $option_ids)))->add_custom_option('bundle_selection_attributes', $this->serializer->serialize($attributes));
                    if ($is_strict_process_mode) {
                        $_result[0]->set_cart_qty($qty);
                    }
                    $result_selection_id = $_result[0]->get_selection_id();
                    $selection_ids[] = $result_selection_id;
                    $unique_key[] = $result_selection_id;
                    $unique_key[] = $qty;
                }
                // "unique" key for bundle selection and add it to selections and bundle for selections
                $unique_key = implode('_', $unique_key);
                foreach ($result as $item) {
                    $item->add_custom_option('bundle_identity', $unique_key);
                }
                $product->add_custom_option('bundle_option_ids', $this->serializer->serialize(array_map('intval', $option_ids)));
                $product->add_custom_option('bundle_selection_ids', $this->serializer->serialize($selection_ids));
                return $result;
            }
        } catch (\Magento\Framework\Exception\Localized_Exception $e) {
            return $e->get_message();
        }
        return $this->get_specify_option_message();
    }
    /**
     * Cast array values to int
     *
     * @param array $array
     * @return int[]|int[][]
     */
    private function recursive_intval(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursive_intval($value);
            } elseif (is_numeric($value) && (int) $value != 0) {
                $array[$key] = (int) $value;
            } else {
                unset($array[$key]);
            }
        }
        return $array;
    }
    /**
     * Retrieve message for specify option(s)
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_specify_option_message()
    {
        return __('Please specify product option(s).');
    }
    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function get_selections_by_ids($selection_ids, $product)
    {
        sort($selection_ids);
        $metadata = $this->metadata_pool->get_metadata(\Magento\Catalog\Api\Data\Product_Interface::class);
        $used_selections = $product->get_data($this->_key_used_selections);
        $used_selections_ids = $product->get_data($this->_key_used_selections_ids);
        if (!$used_selections || $used_selections_ids !== $selection_ids) {
            $store_id = $product->get_store_id();
            /** @var Selections $usedSelections */
            $used_selections = $this->_bundle_collection->create();
            $used_selections->add_attribute_to_select('*')->set_flag('product_children', true)->add_store_filter($this->get_store_filter($product))->set_store_id($store_id)->set_position_order()->add_filter_by_required_options()->set_selection_ids_filter($selection_ids);
            $this->selection_collection_filter_applier->apply($used_selections, 'parent_product_id', $product->get_data($metadata->get_link_field()));
            if (!$this->_catalog_data->is_price_global() && $store_id) {
                $website_id = $this->_store_manager->get_store($store_id)->get_website_id();
                $used_selections->join_prices($website_id);
            }
            $product->set_data($this->_key_used_selections, $used_selections);
            $product->set_data($this->_key_used_selections_ids, $selection_ids);
        }
        return $used_selections;
    }
    /**
     * Retrieve bundle options collection based on ids
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function get_options_by_ids($option_ids, $product)
    {
        sort($option_ids);
        $used_options = $product->get_data($this->_key_used_options);
        $used_options_ids = $product->get_data($this->_key_used_options_ids);
        if (!$used_options || $this->serializer->serialize($used_options_ids) != $this->serializer->serialize($option_ids)) {
            $used_options = $this->_bundle_option->create()->get_resource_collection()->set_product_id_filter($product->get_id())->set_position_order()->join_values($this->_store_manager->get_store()->get_id())->set_id_filter($option_ids);
            $product->set_data($this->_key_used_options, $used_options);
            $product->set_data($this->_key_used_options_ids, $option_ids);
        }
        return $used_options;
    }
    /**
     * Prepare additional options/information for order item which will be created from this product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function get_order_options($product)
    {
        $option_arr = parent::get_order_options($product);
        $bundle_options = [];
        if ($product->has_custom_options()) {
            $custom_option = $product->get_custom_option('bundle_option_ids');
            $option_ids = $this->serializer->unserialize($custom_option->get_value());
            $options = $this->get_options_by_ids($option_ids, $product);
            $custom_option = $product->get_custom_option('bundle_selection_ids');
            $selection_ids = $this->serializer->unserialize($custom_option->get_value());
            $selections = $this->get_selections_by_ids($selection_ids, $product);
            foreach ($selections->get_items() as $selection) {
                if ($selection->is_salable()) {
                    $selection_qty = $product->get_custom_option('selection_qty_' . $selection->get_selection_id());
                    if ($selection_qty) {
                        $price = $product->get_price_model()->get_selection_final_total_price($product, $selection, 0, $selection_qty->get_value());
                        $option = $options->get_item_by_id($selection->get_option_id());
                        if (!isset($bundle_options[$option->get_id()])) {
                            $bundle_options[$option->get_id()] = ['option_id' => $option->get_id(), 'label' => $option->get_title(), 'value' => []];
                        }
                        $bundle_options[$option->get_id()]['value'][] = ['title' => $selection->get_name(), 'qty' => $selection_qty->get_value(), 'price' => $this->price_currency->convert($price)];
                    }
                }
            }
        }
        $option_arr['bundle_options'] = $bundle_options;
        /**
         * Product Prices calculations save
         */
        if ($product->get_price_type()) {
            $option_arr['product_calculations'] = self::CALCULATE_PARENT;
        } else {
            $option_arr['product_calculations'] = self::CALCULATE_CHILD;
        }
        $option_arr['shipment_type'] = $product->get_shipment_type();
        return $option_arr;
    }
    /**
     * Sort selections method for usort function
     *
     * Sort selections by option position, selection position and selection id
     *
     * @param  \Magento\Catalog\Model\Product $firstItem
     * @param  \Magento\Catalog\Model\Product $secondItem
     * @return int
     */
    public function shake_selections($first_item, $second_item)
    {
        $a_position = [$first_item->get_option()->get_position(), $first_item->get_option_id(), $first_item->get_position(), $first_item->get_selection_id()];
        $b_position = [$second_item->get_option()->get_position(), $second_item->get_option_id(), $second_item->get_position(), $second_item->get_selection_id()];
        return $a_position <=> $b_position;
    }
    /**
     * Return true if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function has_options($product)
    {
        $this->set_store_filter($product->get_store_id(), $product);
        $option_ids = $this->get_options_collection($product)->get_all_ids();
        $collection = $this->get_selections_collection($option_ids, $product);
        if ($collection->get_size() > 0 || $product->get_options()) {
            return true;
        }
        return false;
    }
    /**
     * Allow for updates of children qty's
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean true
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_force_child_item_qty_changes($product)
    {
        return true;
    }
    /**
     * Retrieve additional searchable data from type instance
     *
     * Using based on product id and store_id data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function get_searchable_data($product)
    {
        $search_data = parent::get_searchable_data($product);
        $option_search_data = $this->_bundle_option->create()->get_searchable_data($product->get_id(), $product->get_store_id());
        if ($option_search_data) {
            $search_data = array_merge($search_data, $option_search_data);
        }
        return $search_data;
    }
    /**
     * Check if product can be bought
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function check_product_buy_state($product)
    {
        parent::check_product_buy_state($product);
        $product_option_ids = $this->get_options_ids($product);
        $product_selections = $this->get_selections_collection($product_option_ids, $product);
        $selection_ids = $product->get_custom_option('bundle_selection_ids');
        $selection_ids = $this->serializer->unserialize($selection_ids->get_value());
        $buy_request = $product->get_custom_option('info_buyRequest');
        $buy_request = new \Magento\Framework\Data_Object($this->serializer->unserialize($buy_request->get_value()));
        $bundle_option = $buy_request->get_bundle_option();
        if (empty($bundle_option)) {
            throw new \Magento\Framework\Exception\Localized_Exception($this->get_specify_option_message());
        }
        $skip_saleable_check = $this->_catalog_product->get_skip_saleable_check();
        foreach ($selection_ids as $selection_id) {
            /* @var $selection \Magento\Bundle\Model\Selection */
            $selection = $product_selections->get_item_by_id($selection_id);
            if (!$selection || !$selection->is_salable() && !$skip_saleable_check) {
                throw new \Magento\Framework\Exception\Localized_Exception(__('The required options you selected are not available.'));
            }
        }
        $product->get_type_instance()->set_store_filter($product->get_store_id(), $product);
        $options_collection = $this->get_options_collection($product);
        foreach ($options_collection->get_items() as $option) {
            if ($option->get_required() && empty($bundle_option[$option->get_id()])) {
                throw new \Magento\Framework\Exception\Localized_Exception(__('Please select all required options.'));
            }
        }
        return $this;
    }
    /**
     * Retrieve products divided into groups required to purchase
     *
     * At least one product in each group has to be purchased
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function get_products_to_purchase_by_req_groups($product)
    {
        $groups = [];
        $all_products = [];
        $has_required_options = false;
        foreach ($this->get_options($product) as $option) {
            $group_products = [];
            foreach ($this->get_selections_collection([$option->get_id()], $product) as $child_product) {
                $group_products[] = $child_product;
                $all_products[] = $child_product;
            }
            if ($option->get_required()) {
                $groups[] = $group_products;
                $has_required_options = true;
            }
        }
        if (!$has_required_options) {
            $groups = [$all_products];
        }
        return $groups;
    }
    /**
     * Prepare selected options for bundle product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process_buy_request($product, $buy_request)
    {
        $option = $buy_request->get_bundle_option();
        $option_qty = $buy_request->get_bundle_option_qty();
        $option = is_array($option) ? array_filter($option, 'intval') : [];
        $option_qty = is_array($option_qty) ? array_filter($option_qty, 'intval') : [];
        $options = ['bundle_option' => $option, 'bundle_option_qty' => $option_qty];
        return $options;
    }
    /**
     * Check if product can be configured
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function can_configure($product)
    {
        return $product instanceof \Magento\Catalog\Model\Product && $product->is_available() && parent::can_configure($product);
    }
    /**
     * Delete data specific for Bundle product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function delete_type_specific_data(\Magento\Catalog\Model\Product $product)
    {
    }
    // @codingStandardsIgnoreEnd
    /**
     * Return array of specific to type product entities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function get_identities(\Magento\Catalog\Model\Product $product)
    {
        $identities = [];
        $identities[] = parent::get_identities($product);
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($this->get_options($product) as $option) {
            if ($option->get_selections()) {
                /** @var \Magento\Catalog\Model\Product $selection */
                foreach ($option->get_selections() as $selection) {
                    $identities[] = $selection->get_identities();
                }
            }
        }
        return array_merge([], ...$identities);
    }
    /**
     * Returns selection qty
     *
     * @param \Magento\Framework\DataObject $selection
     * @param int[] $qtys
     * @param int $selectionOptionId
     * @return float
     */
    protected function get_qty($selection, $qtys, $selection_option_id)
    {
        if ($selection->get_selection_can_change_qty() && isset($qtys[$selection_option_id])) {
            if (is_array($qtys[$selection_option_id]) && isset($qtys[$selection_option_id][$selection->get_selection_id()])) {
                $selection_qty = $qtys[$selection_option_id][$selection->get_selection_id()];
                $qty = (float) $selection_qty > 0 ? $selection_qty : 1;
            } else {
                $qty = (float) $qtys[$selection_option_id] > 0 ? $qtys[$selection_option_id] : 1;
            }
        } else {
            $qty = (float) $selection->get_selection_qty() ? $selection->get_selection_qty() : 1;
        }
        $qty = (float) $qty;
        return $qty;
    }
    /**
     * Returns qty
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $selection
     * @return float|int
     */
    protected function get_before_qty($product, $selection)
    {
        $before_qty = 0;
        $custom_option = $product->get_custom_option('product_qty_' . $selection->get_id());
        if ($custom_option && $custom_option->get_product()->get_id() == $selection->get_id()) {
            $before_qty = (float) $custom_option->get_value();
            return $before_qty;
        }
        return $before_qty;
    }
    /**
     * Validate required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isStrictProcessMode
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function check_is_all_required_options($product, $is_strict_process_mode, $options_collection, $options)
    {
        if (!$product->get_skip_check_required_option() && $is_strict_process_mode) {
            foreach ($options_collection->get_items() as $option) {
                if ($option->get_required() && empty($options[$option->get_id()])) {
                    throw new \Magento\Framework\Exception\Localized_Exception(__('Please select all required options.'));
                }
            }
        }
    }
    /**
     * Validate Options for Radio and Select input types
     *
     * @param Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validate_radio_and_select_options($options_collection, $options): void
    {
        $error_types = [];
        if (is_array($options_collection->get_items())) {
            foreach ($options_collection->get_items() as $option) {
                if ($this->is_selected_option_valid($option, $options)) {
                    $error_types[] = $option->get_type();
                }
            }
        }
        if (!empty($error_types)) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Option type (%types) should have only one element.', ['types' => implode(', ', $error_types)]));
        }
    }
    /**
     * Check if selected option is valid
     *
     * @param Option $option
     * @param array $options
     * @return bool
     */
    private function is_selected_option_valid($option, $options): bool
    {
        return ($option->get_type() == 'radio' || $option->get_type() == 'select') && isset($options[$option->get_option_id()]) && is_array($options[$option->get_option_id()]) && count($options[$option->get_option_id()]) > 1;
    }
    /**
     * Check if selection is salable
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selections
     * @param bool $skipSaleableCheck
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function check_selections_is_sale($selections, $skip_saleable_check, $options_collection, $options)
    {
        foreach ($selections->get_items() as $selection) {
            if (!$selection->is_salable() && !$skip_saleable_check) {
                $_option = $options_collection->get_item_by_id($selection->get_option_id());
                $option_id = $_option->get_id();
                if (is_array($options[$option_id]) && count($options[$option_id]) > 1) {
                    $more_selections = true;
                } else {
                    $more_selections = false;
                }
                $is_multi_selection = $_option->is_multi_selection();
                if ($_option->get_required() && (!$is_multi_selection || !$more_selections)) {
                    throw new \Magento\Framework\Exception\Localized_Exception(__('The required options you selected are not available.'));
                }
            }
        }
    }
    /**
     * Validate result
     *
     * @param array $_result
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function check_is_result($_result)
    {
        if (is_string($_result)) {
            throw new \Magento\Framework\Exception\Localized_Exception(__($_result));
        }
        if (!isset($_result[0])) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('We can\'t add this item to your shopping cart right now.'));
        }
    }
    /**
     * Merge selections with options
     *
     * @param \Magento\Catalog\Model\Product\Option[] $options
     * @param \Magento\Framework\DataObject[] $selections
     * @return \Magento\Framework\DataObject[]
     */
    protected function merge_selections_with_options($options, $selections)
    {
        $selections = [];
        foreach ($options as $option) {
            $option_selections = $option->get_selections();
            if ($option->get_required() && is_array($option_selections) && count($option_selections) == 1) {
                $selections[] = $option_selections;
            } else {
                $selections = [];
                break;
            }
        }
        return array_merge([], ...$selections);
    }
    /**
     * Get prepared options with selection ids
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @param array $options
     * @return array
     */
    private function get_prepared_options(array $options): array
    {
        foreach ($options as $option_id => $option) {
            foreach ($option as $selection_id => $option_qty) {
                $options[$option_id][$selection_id] = $selection_id;
            }
        }
        return $options;
    }
}