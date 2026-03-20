<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle_Import_Export\Model\Export;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Resource_Model\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Catalog\Model\Resource_Model\Product\Collection;
use Magento\Catalog_Import_Export\Model\Export\Row_Customizer_Interface;
use Magento\Catalog_Import_Export\Model\Import\Product as ImportProductModel;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Import_Export\Model\Import as ImportModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Row_Customizer implements Row_Customizer_Interface
{
    public const BUNDLE_PRICE_TYPE_COL = 'bundle_price_type';
    public const BUNDLE_SKU_TYPE_COL = 'bundle_sku_type';
    public const BUNDLE_PRICE_VIEW_COL = 'bundle_price_view';
    public const BUNDLE_WEIGHT_TYPE_COL = 'bundle_weight_type';
    public const BUNDLE_VALUES_COL = 'bundle_values';
    public const VALUE_FIXED = 'fixed';
    public const VALUE_DYNAMIC = 'dynamic';
    public const VALUE_PERCENT = 'percent';
    public const VALUE_PRICE_RANGE = 'Price range';
    public const VALUE_AS_LOW_AS = 'As low as';
    /**
     * Mapping for bundle types
     *
     * @var array
     */
    protected $type_mapping = ['0' => self::VALUE_DYNAMIC, '1' => self::VALUE_FIXED];
    /**
     * Mapping for price views
     *
     * @var array
     */
    protected $price_view_mapping = ['0' => self::VALUE_PRICE_RANGE, '1' => self::VALUE_AS_LOW_AS];
    /**
     * Mapping for price types
     *
     * @var array
     */
    protected $price_type_mapping = ['0' => self::VALUE_FIXED, '1' => self::VALUE_PERCENT];
    /**
     * Bundle product columns
     *
     * @var array
     */
    protected $bundle_columns = [self::BUNDLE_PRICE_TYPE_COL, self::BUNDLE_SKU_TYPE_COL, self::BUNDLE_PRICE_VIEW_COL, self::BUNDLE_WEIGHT_TYPE_COL, self::BUNDLE_VALUES_COL];
    /**
     * Product's bundle data
     *
     * @var array
     */
    protected $bundle_data = [];
    /**
     * Column name for shipment_type attribute
     *
     * @var string
     */
    private $shipment_type_column = 'bundle_shipment_type';
    /**
     * Mapping for shipment type
     *
     * @var array
     */
    private $shipment_type_mapping = [Abstract_Type::SHIPMENT_TOGETHER => 'together', Abstract_Type::SHIPMENT_SEPARATELY => 'separately'];
    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\Collection[]
     */
    private $option_collections = [];
    /**
     * @var array
     */
    private $store_id_to_code = [];
    /**
     * @var string
     */
    private $option_collection_cache_key = '_cache_instance_options_collection';
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var CatalogData
     */
    private $catalog_data;
    /**
     * @param StoreManagerInterface $storeManager
     * @param CatalogData|null $catalogData
     */
    public function __construct(Store_Manager_Interface $store_manager, ?Catalog_Data $catalog_data = null)
    {
        $this->store_manager = $store_manager;
        $this->catalog_data = $catalog_data ?? Object_Manager::get_instance()->get(Catalog_Data::class);
    }
    /**
     * Retrieve list of bundle specific columns
     *
     * @return array
     */
    private function get_bundle_columns()
    {
        return array_merge($this->bundle_columns, [$this->shipment_type_column]);
    }
    /**
     * Prepare data for export
     *
     * @param Collection $collection
     * @param int[] $productIds
     * @return $this
     */
    public function prepare_data($collection, $product_ids)
    {
        $product_collection = clone $collection;
        $product_collection->add_attribute_to_filter('entity_id', ['in' => $product_ids])->add_attribute_to_filter('type_id', ['eq' => Type::TYPE_BUNDLE]);
        return $this->populate_bundle_data($product_collection);
    }
    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function add_header_columns($columns)
    {
        $columns = array_merge($columns, $this->get_bundle_columns());
        return $columns;
    }
    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function add_data($data_row, $product_id)
    {
        if (!empty($this->bundle_data[$product_id])) {
            $data_row = array_merge($this->clean_not_bundle_additional_attributes($data_row), $this->bundle_data[$product_id]);
        }
        return $data_row;
    }
    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_additional_rows_count($additional_rows_count, $product_id)
    {
        return $additional_rows_count;
    }
    /**
     * Populate bundle product data
     *
     * @param Collection $collection
     * @return $this
     */
    protected function populate_bundle_data($collection)
    {
        foreach ($collection as $product) {
            $id = $product->get_entity_id();
            $this->bundle_data[$id][self::BUNDLE_PRICE_TYPE_COL] = $this->get_type_value($product->get_price_type());
            $this->bundle_data[$id][$this->shipment_type_column] = $this->get_shipment_type_value($product->get_shipment_type());
            $this->bundle_data[$id][self::BUNDLE_SKU_TYPE_COL] = $this->get_type_value($product->get_sku_type());
            $this->bundle_data[$id][self::BUNDLE_PRICE_VIEW_COL] = $this->get_price_view_value($product->get_price_view());
            $this->bundle_data[$id][self::BUNDLE_WEIGHT_TYPE_COL] = $this->get_type_value($product->get_weight_type());
            $this->bundle_data[$id][self::BUNDLE_VALUES_COL] = $this->get_formatted_bundle_option_values($product);
            // cleanup memory
            unset($this->option_collections[$product->get_sku()]);
        }
        return $this;
    }
    /**
     * Retrieve formatted bundle options
     *
     * @param Product $product
     * @return string
     */
    protected function get_formatted_bundle_option_values(Product $product): string
    {
        $option_collections = $this->get_product_option_collection($product);
        $bundle_data = '';
        $option_titles = $this->get_bundle_option_titles($product);
        $options_raw_selections = $this->get_bundle_option_selections($product);
        foreach ($option_collections->get_items() as $option) {
            $option_values = $this->get_formatted_option_values($option, $option_titles);
            $bundle_data .= implode('', array_map(fn($selection_data) => $option_values . Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR . $this->serialize($selection_data) . Import_Product_Model::PSEUDO_MULTI_LINE_SEPARATOR, $options_raw_selections[$option->get_option_id()] ?? []));
        }
        return rtrim($bundle_data, Import_Product_Model::PSEUDO_MULTI_LINE_SEPARATOR);
    }
    /**
     * Retrieve formatted bundle selections
     *
     * @param string $optionValues
     * @param SelectionCollection $selections
     * @return string
     * @deprecared Not used anymore
     */
    protected function get_formatted_bundle_selections($option_values, Selection_Collection $selections)
    {
        $bundle_data = '';
        $selections->add_attribute_to_sort('position');
        foreach ($selections as $selection) {
            $selection_data = ['sku' => $selection->get_sku(), 'price' => $selection->get_selection_price_value(), 'default' => $selection->get_is_default(), 'default_qty' => $selection->get_selection_qty(), 'price_type' => $this->get_price_type_value($selection->get_selection_price_type()), 'can_change_qty' => $selection->get_selection_can_change_qty()];
            $bundle_data .= $option_values . Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR . $this->serialize($selection_data) . Import_Product_Model::PSEUDO_MULTI_LINE_SEPARATOR;
        }
        return $bundle_data;
    }
    /**
     * Retrieve option value of bundle product
     *
     * @param Option $option
     * @param string[] $optionTitles
     * @return string
     */
    protected function get_formatted_option_values(Option $option, array $option_titles = []): string
    {
        $data = [...['name' => $option->get_title()], ...$option_titles[$option->get_option_id()] ?? [], ...['type' => $option->get_type(), 'required' => $option->get_required()]];
        return $this->serialize($data);
    }
    /**
     * Format associative array to serialized string as name1=value1,name2=value2 format
     *
     * @param array $data
     * @return string
     */
    private function serialize(array $data): string
    {
        return implode(Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_map(function ($value, $key) {
            return $key . Import_Product_Model::PAIR_NAME_VALUE_SEPARATOR . $value;
        }, $data, array_keys($data)));
    }
    /**
     * Retrieve bundle type value by code
     *
     * @param string $type
     * @return string
     */
    protected function get_type_value($type)
    {
        $type = (string) $type;
        return $this->type_mapping[$type] ?? self::VALUE_DYNAMIC;
    }
    /**
     * Retrieve bundle price view value by code
     *
     * @param string $type
     * @return string
     */
    protected function get_price_view_value($type)
    {
        $type = (string) $type;
        return $this->price_view_mapping[$type] ?? self::VALUE_PRICE_RANGE;
    }
    /**
     * Retrieve bundle price type value by code
     *
     * @param string $type
     * @return string
     */
    protected function get_price_type_value($type)
    {
        $type = (string) $type;
        return $this->price_type_mapping[$type] ?? null;
    }
    /**
     * Retrieve bundle shipment type value by code
     *
     * @param string $type
     * @return string
     */
    private function get_shipment_type_value($type)
    {
        $type = (string) $type;
        return $this->shipment_type_mapping[$type] ?? null;
    }
    /**
     * Remove bundle specified additional attributes as now they are stored in specified columns
     *
     * @param array $dataRow
     * @return array
     */
    protected function clean_not_bundle_additional_attributes($data_row)
    {
        if (!empty($data_row['additional_attributes'])) {
            $additional_attributes = $this->parse_additional_attributes($data_row['additional_attributes']);
            $data_row['additional_attributes'] = $this->get_not_bundle_attributes($additional_attributes);
        }
        return $data_row;
    }
    /**
     * Retrieve not bundle additional attributes
     *
     * @param array $additionalAttributes
     * @return string
     */
    protected function get_not_bundle_attributes($additional_attributes)
    {
        $filtered_attributes = [];
        foreach ($additional_attributes as $code => $value) {
            if (!in_array('bundle_' . $code, $this->get_bundle_columns())) {
                $filtered_attributes[] = $code . Import_Product_Model::PAIR_NAME_VALUE_SEPARATOR . $value;
            }
        }
        return implode(Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $filtered_attributes);
    }
    /**
     * Retrieves additional attributes as array code=>value.
     *
     * @param string $additionalAttributes
     * @return array
     */
    private function parse_additional_attributes($additional_attributes)
    {
        $attribute_name_value_pairs = explode(Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additional_attributes);
        $prepared_attributes = [];
        $code = '';
        foreach ($attribute_name_value_pairs as $attribute_data) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attribute_data, Import_Product_Model::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $prepared_attributes[$code] .= Import_Model::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR . $attribute_data;
                continue;
            }
            list($code, $value) = explode(Import_Product_Model::PAIR_NAME_VALUE_SEPARATOR, $attribute_data, 2);
            $prepared_attributes[$code] = $value;
        }
        return $prepared_attributes;
    }
    /**
     * Get product options titles.
     *
     * Values for all store views (default) should be specified with 'name' key.
     * If user want to specify value or change existing for non default store views it should be specified with
     * 'name_' prefix and needed store view suffix.
     *
     * For example:
     *  - 'name=All store views name' for all store views
     *  - 'name_specific_store=Specific store name' for store view with 'specific_store' store code
     *
     * @param Product $product
     * @return array
     */
    private function get_bundle_option_titles(Product $product): array
    {
        $option_collections = $this->get_product_option_collection($product);
        $options_titles = [];
        /** @var Option $option */
        foreach ($option_collections->get_items() as $option) {
            $options_titles[$option->get_id()]['name'] = $option->get_title();
        }
        $store_ids = $product->get_store_ids();
        if (count($store_ids) > 1) {
            foreach ($store_ids as $store_id) {
                $option_collections = $this->get_product_option_collection($product, (int) $store_id);
                /** @var Option $option */
                foreach ($option_collections->get_items() as $option) {
                    $option_title = $option->get_title();
                    if ($options_titles[$option->get_id()]['name'] != $option_title) {
                        $options_titles[$option->get_id()]['name_' . $this->get_store_code_by_id((int) $store_id)] = $option_title;
                    }
                }
            }
        }
        return $options_titles;
    }
    /**
     * Get bundle product options selections data
     *
     * The selection price data for the global scope is stored under the 'price' and 'price_type' keys,
     * while for a specific website it is stored under
     * public the 'price_website_<website-code>' and 'price_type_website_<website-code>' keys.
     *
     * @param Product $product
     * @return array
     */
    private function get_bundle_option_selections(Product $product): array
    {
        $selections = $this->get_bundle_option_selections_data($product);
        if (!$this->catalog_data->is_price_global()) {
            foreach ($product->get_website_ids() as $website_id) {
                $website_code = $this->get_website_code_by_id((int) $website_id);
                $store_id = $this->get_website_default_store_id((int) $website_id);
                foreach ($this->get_product_option_collection($product, $store_id) as $option) {
                    foreach ($option->get_selections() as $selection) {
                        $option_id = (string) $option->get_option_id();
                        $selection_id = (string) $selection->get_selection_id();
                        $selection_data = $selections[$option_id][$selection_id] ?? [];
                        if ($selection_data && $selection->get_price_scope() == $website_id) {
                            $selections[$option_id][$selection_id] = [...$selection_data, 'price_website_' . $website_code => $selection->get_selection_price_value(), 'price_type_website_' . $website_code => $this->get_price_type_value($selection->get_selection_price_type())];
                        }
                    }
                }
            }
        }
        return $selections;
    }
    /**
     * Get bundle product options selections data.
     *
     * @param Product $product
     * @param int $storeId
     * @return array
     */
    private function get_bundle_option_selections_data(Product $product, int $store_id = Store::DEFAULT_STORE_ID): array
    {
        $data = [];
        foreach ($this->get_product_option_collection($product, $store_id) as $option) {
            /** @var Option $option*/
            foreach ($option->get_selections() as $selection) {
                /** @var Selection $selection*/
                $option_id = (string) $option->get_option_id();
                $selection_id = (string) $selection->get_selection_id();
                $data[$option_id][$selection_id] = ['sku' => $selection->get_sku(), 'price' => $selection->get_selection_price_value(), 'default' => $selection->get_is_default(), 'default_qty' => $selection->get_selection_qty(), 'price_type' => $this->get_price_type_value($selection->get_selection_price_type()), 'can_change_qty' => $selection->get_selection_can_change_qty()];
            }
        }
        return $data;
    }
    /**
     * Get product options collection by provided product model.
     *
     * Set given store id to the product if it was defined (default store id will be set if was not).
     *
     * @param Product $product $product
     * @param int $storeId
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    private function get_product_option_collection(Product $product, int $store_id = Store::DEFAULT_STORE_ID): \Magento\Bundle\Model\Resource_Model\Option\Collection
    {
        $product_sku = $product->get_sku();
        if (!isset($this->option_collections[$product_sku][$store_id])) {
            $product->unset_data($this->option_collection_cache_key);
            $product->set_store_id($store_id);
            $option_collection = $product->get_type_instance()->get_options_collection($product)->set_order('position', Collection::SORT_ORDER_ASC);
            // Ensure children products are not filtered by website.
            // We need to export all children products regardless of the website they are assigned to.
            $product->get_type_instance()->set_store_filter(Store::DEFAULT_STORE_ID, $product);
            $selection_collection = $product->get_type_instance()->get_selections_collection($product->get_type_instance()->get_options_ids($product), $product)->set_order('position', Collection::SORT_ORDER_ASC)->add_attribute_to_sort('position', Collection::SORT_ORDER_ASC);
            $option_collection->append_selections($selection_collection, true);
            $this->option_collections[$product_sku][$store_id] = $option_collection;
        }
        return $this->option_collections[$product_sku][$store_id];
    }
    /**
     * Retrieve default store id for website
     *
     * @param int $websiteId
     * @return int
     * @throws LocalizedException
     */
    private function get_website_default_store_id(int $website_id): int
    {
        return (int) $this->store_manager->get_group($this->store_manager->get_website($website_id)->get_default_group_id())->get_default_store_id();
    }
    /**
     * Retrieve website code by its ID.
     *
     * @param int $websiteId
     * @return string
     * @throws LocalizedException
     */
    private function get_website_code_by_id(int $website_id): string
    {
        return $this->store_manager->get_website($website_id)->get_code();
    }
    /**
     * Retrieve store code by it's ID.
     *
     * Collect store id in $storeIdToCode[] private variable if it was not initialized earlier.
     *
     * @param int $storeId
     * @return string
     */
    private function get_store_code_by_id(int $store_id): string
    {
        if (!isset($this->store_id_to_code[$store_id])) {
            $this->store_id_to_code[$store_id] = $this->store_manager->get_store($store_id)->get_code();
        }
        return $this->store_id_to_code[$store_id];
    }
}