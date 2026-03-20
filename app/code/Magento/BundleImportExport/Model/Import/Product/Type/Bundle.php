<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle_Import_Export\Model\Import\Product\Type;

use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product\Type\Abstract_Type;
use Magento\Catalog\Model\Resource_Model\Product\Attribute\Collection_Factory as AttributeCollectionFactory;
use Magento\Catalog_Import_Export\Model\Import\Product;
use Magento\Catalog_Import_Export\Model\Import\Product\Type\Abstract_Type as CatalogImportExportAbstractType;
use Magento\Eav\Model\Resource_Model\Entity\Attribute\Set\Collection_Factory as AttributeSetCollectionFactory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
use Magento\Import_Export\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Import entity Bundle product type.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle extends Catalog_Import_Export_Abstract_Type implements Reset_After_Request_Interface
{
    /**
     * Delimiter before product option value.
     */
    public const BEFORE_OPTION_VALUE_DELIMITER = ';';
    public const PAIR_VALUE_SEPARATOR = '=';
    /**
     * Dynamic value.
     */
    public const VALUE_DYNAMIC = 'dynamic';
    /**
     * Fixed value.
     */
    public const VALUE_FIXED = 'fixed';
    public const NOT_FIXED_DYNAMIC_ATTRIBUTE = 'price_view';
    public const SELECTION_PRICE_TYPE_FIXED = 0;
    public const SELECTION_PRICE_TYPE_PERCENT = 1;
    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cached_options = [];
    /**
     * Array of cached skus.
     *
     * @var array
     */
    protected $_cached_skus = [];
    /**
     * Mapping array between cached skus and products.
     *
     * @var array
     */
    protected $_cached_sku_to_products = [];
    /**
     * Array of queries selecting cached options.
     *
     * @var array
     */
    protected $_cached_option_select_query = [];
    /**
     * Column names that holds values with particular meaning.
     *
     * @var string[]
     */
    protected $_special_attributes = ['price_type', 'weight_type', 'sku_type'];
    /**
     * Custom fields mapping for bundle product.
     *
     * @var array
     */
    protected $_custom_fields_mapping = ['price_type' => 'bundle_price_type', 'shipment_type' => 'bundle_shipment_type', 'price_view' => 'bundle_price_view', 'weight_type' => 'bundle_weight_type', 'sku_type' => 'bundle_sku_type'];
    /**
     * Bundle field mapping for bundle product with selection.
     *
     * @var array
     */
    protected $_bundle_field_mapping = ['is_default' => 'default', 'selection_price_value' => 'price', 'selection_qty' => 'default_qty'];
    /**
     * Option type mapping for bundle product.
     *
     * @var array
     */
    protected $_option_type_mapping = ['dropdown' => 'select', 'radiobutton' => 'radio', 'checkbox' => 'checkbox', 'multiselect' => 'multi'];
    /**
     * @var Bundle\RelationsDataSaver
     */
    private $relations_data_saver;
    /**
     * @var StoreManagerInterface
     */
    private $store_manager;
    /**
     * @var array
     */
    private $store_code_to_id = [];
    /**
     * @var array
     */
    private array $website_code_to_id = [];
    /**
     * @var CatalogData
     */
    private $catalog_data;
    /**
     * @param AttributeSetCollectionFactory $attrSetColFac
     * @param AttributeCollectionFactory $prodAttrColFac
     * @param ResourceConnection $resource
     * @param array $params
     * @param MetadataPool|null $metadataPool
     * @param Bundle\RelationsDataSaver|null $relationsDataSaver
     * @param StoreManagerInterface|null $storeManager
     * @param CatalogData|null $catalogData
     */
    public function __construct(Attribute_Set_Collection_Factory $attr_set_col_fac, Attribute_Collection_Factory $prod_attr_col_fac, Resource_Connection $resource, array $params, ?Metadata_Pool $metadata_pool = null, ?Bundle\Relations_Data_Saver $relations_data_saver = null, ?Store_Manager_Interface $store_manager = null, ?Catalog_Data $catalog_data = null)
    {
        parent::__construct($attr_set_col_fac, $prod_attr_col_fac, $resource, $params, $metadata_pool);
        $this->relations_data_saver = $relations_data_saver ?: Object_Manager::get_instance()->get(Bundle\Relations_Data_Saver::class);
        $this->store_manager = $store_manager ?: Object_Manager::get_instance()->get(Store_Manager_Interface::class);
        $this->catalog_data = $catalog_data ?? Object_Manager::get_instance()->get(Catalog_Data::class);
    }
    /**
     * Parse selections.
     *
     * @param array $rowData
     * @param int $entityId
     *
     * @return array
     */
    protected function parse_selections($row_data, $entity_id)
    {
        if (empty($row_data['bundle_values'])) {
            return [];
        }
        if (is_string($row_data['bundle_values'])) {
            $row_data['bundle_values'] = str_replace(self::BEFORE_OPTION_VALUE_DELIMITER, $this->_entity_model->get_multiple_value_separator(), $row_data['bundle_values']);
            $selections = explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $row_data['bundle_values']);
        } else {
            $selections = $row_data['bundle_values'];
        }
        foreach ($selections as $selection) {
            $option = is_string($selection) ? $this->parse_option(explode($this->_entity_model->get_multiple_value_separator(), $selection)) : $selection;
            if (isset($option['sku'], $option['name'])) {
                $this->_cached_skus[] = $option['sku'];
                if (!isset($this->_cached_options[$entity_id][$option['name']])) {
                    $this->_cached_options[$entity_id][$option['name']] = [];
                    $this->_cached_options[$entity_id][$option['name']] = $option;
                    $this->_cached_options[$entity_id][$option['name']]['selections'] = [];
                }
                $this->_cached_options[$entity_id][$option['name']]['selections'][$option['sku']] = $option;
                $this->_cached_option_select_query[] = [(int) $entity_id, $option['name']];
            }
        }
        return $selections;
    }
    /**
     * Parse the option.
     *
     * @param array $values
     *
     * @return array
     */
    protected function parse_option($values)
    {
        $option = [];
        foreach ($values as $key_value) {
            $key_value = $key_value ? trim($key_value) : '';
            $pos = strpos($key_value, self::PAIR_VALUE_SEPARATOR);
            if ($pos !== false) {
                $key = substr($key_value, 0, $pos);
                $value = substr($key_value, $pos + 1);
                if ($key == 'type') {
                    if (isset($this->_option_type_mapping[$value])) {
                        $value = $this->_option_type_mapping[$value];
                    }
                }
                $option[$key] = $value;
            }
        }
        return $option;
    }
    /**
     * Populate the option template.
     *
     * @param array $option
     * @param int $entityId
     * @param int $index
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populate_option_template($option, $entity_id, $index = null)
    {
        $populated_option = ['option_id' => null, 'parent_id' => $entity_id, 'required' => isset($option['required']) ? $option['required'] : 1, 'position' => $index === null ? 0 : $index, 'type' => isset($option['type']) ? $option['type'] : 'select'];
        if (isset($option['option_id'])) {
            $populated_option['option_id'] = $option['option_id'];
        }
        return $populated_option;
    }
    /**
     * Populate the option value template.
     *
     * @param array $option
     * @param int $optionId
     * @param int $storeId
     * @return array
     */
    protected function populate_option_value_template(array $option, int $option_id, int $store_id = 0): array
    {
        $option_values = [];
        if (isset($option['name'], $option['parent_id']) && $option_id) {
            $pattern = '/^name[_]?(.*)/';
            $keys = array_keys($option);
            $option_names = preg_grep($pattern, $keys);
            foreach ($option_names as $option_name) {
                preg_match($pattern, $option_name, $store_codes);
                $store_code = array_pop($store_codes);
                $store_id = $store_code ? $this->get_store_id_by_code($store_code) : $store_id;
                $option_values[] = ['option_id' => $option_id, 'parent_product_id' => $option['parent_id'], 'store_id' => $store_id, 'title' => $option[$option_name]];
            }
        }
        return $option_values;
    }
    /**
     * Populate the option value template.
     *
     * @param array $selection
     * @param int $optionId
     * @param int $parentId
     * @param int $index
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function populate_selection_template($selection, $option_id, $parent_id, $index)
    {
        if (!isset($selection['parent_product_id'])) {
            if (!isset($this->_cached_sku_to_products[$selection['sku']])) {
                return false;
            }
            $product_id = $this->_cached_sku_to_products[$selection['sku']];
        } else {
            $product_id = $selection['product_id'];
        }
        $populated_selection = ['selection_id' => null, 'option_id' => (int) $option_id, 'parent_product_id' => (int) $parent_id, 'product_id' => (int) $product_id, 'position' => (int) $index, 'is_default' => isset($selection['default']) && $selection['default'] ? 1 : 0, 'selection_price_type' => isset($selection['price_type']) && $selection['price_type'] == self::VALUE_FIXED ? self::SELECTION_PRICE_TYPE_FIXED : self::SELECTION_PRICE_TYPE_PERCENT, 'selection_price_value' => isset($selection['price']) ? (float) $selection['price'] : 0.0, 'selection_qty' => isset($selection['default_qty']) ? (float) $selection['default_qty'] : 1.0, 'selection_can_change_qty' => isset($selection['can_change_qty']) ? $selection['can_change_qty'] ? 1 : 0 : 1];
        if (isset($selection['selection_id'])) {
            $populated_selection['selection_id'] = $selection['selection_id'];
        }
        return $populated_selection;
    }
    /**
     * Set cache option selection
     *
     * @param array $existingSelection
     * @param string $optionTitle
     * @param string $selectIndex
     * @param string $key
     * @param string $origKey
     * @return void
     */
    private function set_cache_option_selection(array $existing_selection, string $option_title, string $select_index, string $key, string $orig_key): void
    {
        if (!isset($this->_cached_options[$existing_selection['parent_product_id']][$option_title]['selections'][$select_index][$key])) {
            $this->_cached_options[$existing_selection['parent_product_id']][$option_title]['selections'][$select_index][$key] = $existing_selection[$orig_key];
        }
    }
    /**
     * Deprecated method for retrieving mapping between skus and products.
     *
     * @deprecated 100.3.0 Misspelled method
     * @see retrieveProductsByCachedSkus
     */
    protected function retrieve_producs_by_cached_skus()
    {
        return $this->retrieve_products_by_cached_skus();
    }
    /**
     * Retrieve mapping between skus and products.
     *
     * @return CatalogImportExportAbstractType
     */
    protected function retrieve_products_by_cached_skus()
    {
        $this->_cached_sku_to_products = $this->connection->fetch_pairs($this->connection->select()->from($this->_resource->get_table_name('catalog_product_entity'), ['sku', 'entity_id'])->where('sku IN (?)', $this->_cached_skus));
        return $this;
    }
    /**
     * Save product type specific data.
     *
     * @return CatalogImportExportAbstractType
     */
    public function save_data()
    {
        if ($this->_entity_model->get_behavior() == Import::BEHAVIOR_DELETE) {
            $product_ids = [];
            $new_sku = $this->_entity_model->get_new_sku();
            while ($bunch = $this->_entity_model->get_next_bunch()) {
                foreach ($bunch as $row_data) {
                    $product_data = $new_sku[strtolower($row_data[Product::COL_SKU] ?? '')];
                    $product_ids[] = $product_data[$this->get_product_entity_link_field()];
                }
                $this->delete_options_and_selections($product_ids);
            }
        } else {
            $new_sku = $this->_entity_model->get_new_sku();
            while ($bunch = $this->_entity_model->get_next_bunch()) {
                foreach ($bunch as $row_num => $row_data) {
                    if (!$this->_entity_model->is_row_allowed_to_import($row_data, $row_num)) {
                        continue;
                    }
                    $product_data = $new_sku[strtolower($row_data[Product::COL_SKU] ?? '')];
                    if ($this->_type != $product_data['type_id']) {
                        continue;
                    }
                    $this->parse_selections($row_data, $product_data[$this->get_product_entity_link_field()]);
                }
                if (!empty($this->_cached_options)) {
                    $this->retrieve_products_by_cached_skus();
                    $this->populate_existing_options();
                    $this->insert_options();
                    $this->insert_selections();
                    $this->insert_parent_child_relations();
                    $this->clear();
                }
            }
        }
        return $this;
    }
    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct
     * @return bool
     */
    public function is_row_valid(array $row_data, $row_num, $is_new_product = true)
    {
        if (isset($row_data['bundle_price_type']) && $row_data['bundle_price_type'] == 'dynamic') {
            $row_data['price'] = isset($row_data['price']) && $row_data['price'] ? $row_data['price'] : '0.00';
        }
        return parent::is_row_valid($row_data, $row_num, $is_new_product);
    }
    /**
     * Prepare attributes with default value for save.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     * @return array
     */
    public function prepare_attributes_with_default_value_for_save(array $row_data, $with_default_value = true)
    {
        $result_attrs = parent::prepare_attributes_with_default_value_for_save($row_data, $with_default_value);
        $result_attrs = array_merge($result_attrs, $this->transform_bundle_custom_attributes($row_data));
        return $result_attrs;
    }
    /**
     * Transform dynamic/fixed values to integer.
     *
     * @param array $rowData
     * @return array
     */
    protected function transform_bundle_custom_attributes($row_data)
    {
        $result_attrs = [];
        foreach ($this->_custom_fields_mapping as $old_key => $new_key) {
            if (isset($row_data[$old_key])) {
                switch ($new_key) {
                    case $this->_custom_fields_mapping['price_view']:
                        break;
                    case $this->_custom_fields_mapping['shipment_type']:
                        $result_attrs[$old_key] = $row_data[$old_key] == 'separately' ? Abstract_Type::SHIPMENT_SEPARATELY : Abstract_Type::SHIPMENT_TOGETHER;
                        break;
                    default:
                        $result_attrs[$old_key] = $row_data[$old_key] == self::VALUE_FIXED ? Bundle_Price::PRICE_TYPE_FIXED : Bundle_Price::PRICE_TYPE_DYNAMIC;
                }
            }
        }
        return $result_attrs;
    }
    /**
     * Populates existing options.
     *
     * @return CatalogImportExportAbstractType
     */
    protected function populate_existing_options()
    {
        $select = $this->connection->select()->from(['bo' => $this->_resource->get_table_name('catalog_product_bundle_option')], ['option_id', 'parent_id', 'required', 'position', 'type'])->join_left(['bov' => $this->_resource->get_table_name('catalog_product_bundle_option_value')], 'bo.option_id = bov.option_id', ['value_id', 'title']);
        $or_where = false;
        foreach ($this->_cached_option_select_query as $item) {
            if ($or_where) {
                $select->or_where('parent_id = ' . $item[0] . ' AND title = ?', $item[1]);
            } else {
                $select->where('parent_id = ' . $item[0] . ' AND title = ?', $item[1]);
                $or_where = true;
            }
        }
        $existing_options = $this->connection->fetch_assoc($select);
        foreach ($existing_options as $option_id => $option) {
            $this->_cached_options[$option['parent_id']][$option['title']]['option_id'] = $option_id;
            foreach ($option as $key => $value) {
                if (!isset($this->_cached_options[$option['parent_id']][$option['title']][$key])) {
                    $this->_cached_options[$option['parent_id']][$option['title']][$key] = $value;
                }
            }
        }
        $this->populate_existing_selections($existing_options);
        return $this;
    }
    /**
     * Populate existing selections.
     *
     * @param array $existingOptions
     *
     * @return CatalogImportExportAbstractType
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populate_existing_selections($existing_options)
    {
        //@codingStandardsIgnoreStart
        $existing_selections = $this->connection->fetch_all($this->connection->select()->from($this->_resource->get_table_name('catalog_product_bundle_selection'))->where('option_id IN (?)', array_keys($existing_options)));
        foreach ($existing_selections as $existing_selection) {
            $option_title = $existing_options[$existing_selection['option_id']]['title'];
            if (array_key_exists($existing_selection['parent_product_id'], $this->_cached_options)) {
                $cached_options_selections = $this->_cached_options[$existing_selection['parent_product_id']][$option_title]['selections'];
                foreach ($cached_options_selections as $select_index => $selection) {
                    $product_id = $this->_cached_sku_to_products[$selection['sku']];
                    if ($product_id == $existing_selection['product_id']) {
                        foreach (array_keys($existing_selection) as $orig_key) {
                            $key = $this->_bundle_field_mapping[$orig_key] ?? $orig_key;
                            $this->set_cache_option_selection($existing_selection, (string) $option_title, (string) $select_index, (string) $key, (string) $orig_key);
                        }
                        break;
                    }
                }
            }
        }
        // @codingStandardsIgnoreEnd
        return $this;
    }
    /**
     * Insert options.
     *
     * @return CatalogImportExportAbstractType
     */
    protected function insert_options()
    {
        $product_ids = [];
        $insert = [];
        foreach ($this->_cached_options as $entity_id => $options) {
            $index = 0;
            $product_ids[] = $entity_id;
            foreach ($options as $key => $option) {
                if (isset($option['position'])) {
                    $index = $option['position'];
                }
                if ($tmp_array = $this->populate_option_template($option, $entity_id, $index)) {
                    $insert[] = $tmp_array;
                    $this->_cached_options[$entity_id][$key]['index'] = $index;
                    $index++;
                }
            }
        }
        $this->relations_data_saver->save_options($insert);
        $option_ids = $this->connection->fetch_assoc($this->connection->select()->from(['bo' => $this->_resource->get_table_name('catalog_product_bundle_option')], ['option_id', 'position', 'parent_id'])->join_left(['bov' => $this->_resource->get_table_name('catalog_product_bundle_option_value')], 'bo.option_id = bov.option_id', ['title'])->where('parent_id IN (?)', $product_ids));
        $this->relations_data_saver->save_option_values($this->populate_insert_option_values($option_ids));
        return $this;
    }
    /**
     * Populate array for insert option values
     *
     * @param array $optionIds
     * @return array
     */
    protected function populate_insert_option_values(array $option_ids): array
    {
        $option_values = [];
        foreach ($this->_cached_options as $entity_id => $options) {
            foreach ($options as $key => $option) {
                foreach ($option_ids as $option_id => $assoc) {
                    if ($assoc['position'] == $this->_cached_options[$entity_id][$key]['index'] && $assoc['parent_id'] == $entity_id && (empty($assoc['title']) || $assoc['title'] == $this->_cached_options[$entity_id][$key]['name'])) {
                        $option['parent_id'] = $entity_id;
                        $option_values[] = $this->populate_option_value_template($option, $option_id);
                        $this->_cached_options[$entity_id][$key]['option_id'] = $option_id;
                        break;
                    }
                }
            }
        }
        return array_merge([], ...$option_values);
    }
    /**
     * Insert selections.
     *
     * @return CatalogImportExportAbstractType
     */
    protected function insert_selections()
    {
        $selections = [];
        $option_ids = [];
        foreach ($this->_cached_options as $product_id => $options) {
            foreach ($options as $option) {
                $index = 0;
                foreach ($option['selections'] as $selection) {
                    if (isset($selection['position'])) {
                        $index = $selection['position'];
                    }
                    $option_ids[$option['option_id']] = $option['option_id'];
                    if ($tmp_array = $this->populate_selection_template($selection, $option['option_id'], $product_id, $index)) {
                        $selections[] = $tmp_array;
                        $index++;
                    }
                }
            }
        }
        $this->relations_data_saver->save_selections($selections);
        if (!empty($option_ids) && !$this->catalog_data->is_price_global()) {
            $this->save_selections_prices($option_ids);
        }
        return $this;
    }
    /**
     * Insert selections prices for websites.
     *
     * @param array $optionIds
     * @return void
     */
    private function save_selections_prices(array $option_ids): void
    {
        $selection_prices = $this->get_selections_prices();
        if (empty($selection_prices)) {
            return;
        }
        $select = $this->connection->select()->from($this->_resource->get_table_name('catalog_product_bundle_selection'))->where('option_id IN (?)', array_keys($option_ids));
        $existing_selections = $this->connection->fetch_all($select);
        $selection_prices_to_insert = [];
        foreach ($existing_selections as $selection) {
            $prices = $selection_prices[$selection['parent_product_id']][$selection['option_id']][$selection['product_id']] ?? [];
            foreach ($prices as $website_id => $data) {
                $selection_prices_to_insert[] = ['selection_id' => $selection['selection_id'], 'parent_product_id' => $selection['parent_product_id'], 'website_id' => $website_id, 'selection_price_type' => $data['selection_price_type'] ?? $selection['selection_price_type'], 'selection_price_value' => $data['selection_price_value']];
            }
        }
        if (!empty($selection_prices_to_insert)) {
            $this->relations_data_saver->save_selection_prices($selection_prices_to_insert);
        }
    }
    /**
     * Returns selections prices by parentProductId, optionId, productId, and websiteId.
     *
     * @return array
     */
    private function get_selections_prices(): array
    {
        $selection_prices = [];
        foreach ($this->_cached_options as $parent_product_id => $options) {
            foreach ($options as $option) {
                foreach ($option['selections'] as $selection) {
                    $product_id = $this->_cached_sku_to_products[$selection['sku']] ?? null;
                    if (!$product_id) {
                        continue;
                    }
                    $selection_prices[$parent_product_id][$option['option_id']][$product_id] = $this->get_selection_prices($selection);
                }
            }
        }
        return $selection_prices;
    }
    /**
     * Returns selection prices by websiteId.
     *
     * @param array $selection
     * @return array
     */
    private function get_selection_prices(array $selection): array
    {
        $prices = [];
        foreach ($selection as $key => $value) {
            $value = trim($value);
            if (is_numeric($value) && str_starts_with($key, 'price_website_')) {
                $website_code = str_replace('price_website_', '', $key);
                $website_id = $this->get_website_id_by_code($website_code);
                $price_type = $selection['price_type_website_' . $website_code] ?? $selection['price_type'] ?? null;
                $prices[$website_id] = ['selection_price_value' => (float) $value, 'selection_price_type' => match ($price_type) {
                    self::VALUE_FIXED => self::SELECTION_PRICE_TYPE_FIXED,
                    default => self::SELECTION_PRICE_TYPE_PERCENT,
                }];
            }
        }
        return $prices;
    }
    /**
     * Get website id by website code.
     *
     * @param string $websiteCode
     * @return int
     */
    private function get_website_id_by_code(string $website_code): int
    {
        if (!isset($this->website_code_to_id[$website_code])) {
            $this->website_code_to_id = [];
            foreach ($this->store_manager->get_websites() as $website) {
                $this->website_code_to_id[$website->get_code()] = (int) $website->get_id();
            }
        }
        return $this->website_code_to_id[$website_code] ?? Store::DEFAULT_STORE_ID;
    }
    /**
     * Insert parent/child product relations
     *
     * @return CatalogImportExportAbstractType
     */
    private function insert_parent_child_relations()
    {
        foreach ($this->_cached_options as $product_id => $options) {
            $child_ids = [];
            foreach ($options as $option) {
                foreach ($option['selections'] as $selection) {
                    if (isset($this->_cached_sku_to_products[$selection['sku']])) {
                        $child_ids[] = $this->_cached_sku_to_products[$selection['sku']];
                    }
                }
                $this->relations_data_saver->save_product_relations($product_id, $child_ids);
            }
        }
        return $this;
    }
    /**
     * Initialize attributes parameters for all attributes' sets.
     *
     * @return $this
     */
    protected function _init_attributes()
    {
        parent::_init_attributes();
        $options = [self::VALUE_DYNAMIC => Bundle_Price::PRICE_TYPE_DYNAMIC, self::VALUE_FIXED => Bundle_Price::PRICE_TYPE_FIXED];
        foreach ($this->_special_attributes as $attribute_code) {
            if (isset(self::$attribute_code_to_id[$attribute_code]) && $id = self::$attribute_code_to_id[$attribute_code]) {
                self::$common_attributes_cache[$id]['type'] = 'select';
                self::$common_attributes_cache[$id]['options'] = $options;
                foreach ($this->_attributes as $attr_set_name => $attr_set_value) {
                    if (isset($attr_set_value[$attribute_code])) {
                        $this->_attributes[$attr_set_name][$attribute_code]['type'] = 'select';
                        $this->_attributes[$attr_set_name][$attribute_code]['options'] = $options;
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Delete options and selections.
     *
     * @param array $productIds
     *
     * @return CatalogImportExportAbstractType
     */
    protected function delete_options_and_selections($product_ids)
    {
        if (empty($product_ids)) {
            return $this;
        }
        $option_table = $this->_resource->get_table_name('catalog_product_bundle_option');
        $option_value_table = $this->_resource->get_table_name('catalog_product_bundle_option_value');
        $selection_table = $this->_resource->get_table_name('catalog_product_bundle_selection');
        $values_ids = $this->connection->fetch_assoc($this->connection->select()->from(['bov' => $option_value_table], ['value_id'])->join_left(['bo' => $option_table], 'bo.option_id = bov.option_id', ['option_id'])->where('parent_id IN (?)', $product_ids));
        $this->connection->delete($option_value_table, $this->connection->quote_into('value_id IN (?)', array_keys($values_ids)));
        $this->connection->delete($option_table, $this->connection->quote_into('parent_id IN (?)', $product_ids));
        $this->connection->delete($selection_table, $this->connection->quote_into('parent_product_id IN (?)', $product_ids));
        return $this;
    }
    /**
     * Clear cached values between bunches
     *
     * @return CatalogImportExportAbstractType
     */
    protected function clear()
    {
        $this->_cached_options = [];
        $this->_cached_option_select_query = [];
        $this->_cached_skus = [];
        $this->_cached_sku_to_products = [];
        return $this;
    }
    /**
     * Get store id by store code.
     *
     * @param string $storeCode
     * @return int
     */
    private function get_store_id_by_code(string $store_code): int
    {
        if (!isset($this->store_code_to_id[$store_code])) {
            /** @var $store Store */
            foreach ($this->store_manager->get_stores() as $store) {
                $this->store_code_to_id[$store->get_code()] = (int) $store->get_id();
            }
        }
        return $this->store_code_to_id[$store_code];
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->_cached_options = [];
        $this->_cached_skus = [];
        $this->_cached_option_select_query = [];
        $this->_cached_sku_to_products = [];
    }
}