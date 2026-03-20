<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Pricing_Import_Export\Model\Export;

use Magento\Advanced_Pricing_Import_Export\Model\Import\Advanced_Pricing as ImportAdvancedPricing;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Import_Export\Model\Export;
use Magento\Store\Model\Store;
/**
 * Export Advanced Pricing
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Advanced_Pricing extends \Magento\Catalog_Import_Export\Model\Export\Product
{
    public const ENTITY_ADVANCED_PRICING = 'advanced_pricing';
    /**
     * @var string
     */
    protected $_entity_type_code;
    /**
     * @var int
     */
    protected $_pass_tier_price = 0;
    /**
     * List of items websites
     *
     * @var array
     */
    protected $_price_website = [Import_Advanced_Pricing::COL_TIER_PRICE_WEBSITE];
    /**
     * List of items customer groups
     *
     * @var array
     */
    protected $_price_customer_group = [Import_Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP];
    /**
     * Export template
     *
     * @var array
     */
    protected $template_export_data = [Import_Advanced_Pricing::COL_SKU => '', Import_Advanced_Pricing::COL_TIER_PRICE_WEBSITE => '', Import_Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP => '', Import_Advanced_Pricing::COL_TIER_PRICE_QTY => '', Import_Advanced_Pricing::COL_TIER_PRICE => '', Import_Advanced_Pricing::COL_TIER_PRICE_TYPE => ''];
    /**
     * @var string[]
     */
    private $website_codes_map = [];
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\Timezone_Interface $locale_date, \Magento\Eav\Model\Config $config, protected \Magento\Framework\App\Resource_Connection $_resource, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Psr\Log\Logger_Interface $logger, \Magento\Catalog\Model\Resource_Model\Product\Collection_Factory $collection_factory, \Magento\Import_Export\Model\Export\Config_Interface $export_config, \Magento\Catalog\Model\Resource_Model\Product_Factory $product_factory, \Magento\Eav\Model\Resource_Model\Entity\Attribute\Set\Collection_Factory $attr_set_col_factory, \Magento\Catalog\Model\Resource_Model\Category\Collection_Factory $category_col_factory, \Magento\Catalog_Inventory\Model\Resource_Model\Stock\Item_Factory $item_factory, \Magento\Catalog\Model\Resource_Model\Product\Option\Collection_Factory $option_col_factory, \Magento\Catalog\Model\Resource_Model\Product\Attribute\Collection_Factory $attribute_col_factory, \Magento\Catalog_Import_Export\Model\Export\Product\Type\Factory $_type_factory, \Magento\Catalog\Model\Product\Link_Type_Provider $link_type_provider, \Magento\Catalog_Import_Export\Model\Export\Row_Customizer_Interface $row_customizer, protected \Magento\Catalog_Import_Export\Model\Import\Product\Store_Resolver $_store_resolver, protected \Magento\Customer\Api\Group_Repository_Interface $_group_repository)
    {
        parent::__construct($locale_date, $config, $this->_resource, $store_manager, $logger, $collection_factory, $export_config, $product_factory, $attr_set_col_factory, $category_col_factory, $item_factory, $option_col_factory, $attribute_col_factory, $_type_factory, $link_type_provider, $row_customizer);
    }
    /**
     * Init type models
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function init_type_models(): static
    {
        $product_types = $this->_export_config->get_entity_types(Catalog_Product::ENTITY);
        $disabled_attrs = [];
        $index_value_attributes = [];
        foreach ($product_types as $product_type_name => $product_type_config) {
            if (!$model = $this->_type_factory->create($product_type_config['model'])) {
                throw new \Magento\Framework\Exception\Localized_Exception(__('Entity type model \'%1\' is not found', $product_type_config['model']));
            }
            if (!$model instanceof \Magento\Catalog_Import_Export\Model\Export\Product\Type\Abstract_Type) {
                throw new \Magento\Framework\Exception\Localized_Exception(__('Entity type model must be an instance of' . ' \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType'));
            }
            if ($model->is_suitable()) {
                $this->_product_type_models[$product_type_name] = $model;
                $disabled_attrs[] = $model->get_disabled_attrs();
                $index_value_attributes[] = $model->get_index_value_attributes();
            }
        }
        if (!$this->_product_type_models) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('There are no product types available for export'));
        }
        $this->_disabled_attrs = array_unique(array_merge([], $this->_disabled_attrs, ...$disabled_attrs));
        $this->_index_value_attributes = array_unique(array_merge([], $this->_index_value_attributes, ...$index_value_attributes));
        return $this;
    }
    /**
     * Export process
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        //Execution time may be very long
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);
        $writer = $this->get_writer();
        $page = 0;
        while (true) {
            ++$page;
            $entity_collection = $this->_get_entity_collection(true);
            $entity_collection->set_order('has_options', 'asc');
            $entity_collection->set_store_id(Store::DEFAULT_STORE_ID);
            $this->_prepare_entity_collection($entity_collection);
            $this->paginate_collection($page, $this->get_items_per_page());
            if ($entity_collection->count() == 0) {
                break;
            }
            $entity_collection->clear();
            $export_data = $this->get_export_data();
            foreach ($export_data as $data_row) {
                $writer->write_row($data_row);
            }
            if ($entity_collection->get_cur_page() >= $entity_collection->get_last_page_number()) {
                break;
            }
        }
        return $writer->get_contents();
    }
    /**
     * Clean up attribute collection.
     */
    public function filter_attribute_collection(\Magento\Eav\Model\Resource_Model\Entity\Attribute\Collection $collection): \Magento\Eav\Model\Resource_Model\Entity\Attribute\Collection
    {
        $collection->load();
        foreach ($collection as $attribute) {
            if (in_array($attribute->get_attribute_code(), $this->_disabled_attrs)) {
                $collection->remove_item_by_key($attribute->get_id());
            }
        }
        return $collection;
    }
    /**
     * Get export data for collection
     *
     * @return mixed[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function get_export_data(): array
    {
        if ($this->_pass_tier_price) {
            return [];
        }
        $export_data = [];
        try {
            $products_by_stores = $this->load_collection();
            if (!empty($products_by_stores)) {
                $link_field = $this->get_product_entity_link_field();
                $product_link_ids = [];
                foreach ($products_by_stores as $product) {
                    $product_link_ids[array_pop($product)[$link_field]] = true;
                }
                $product_link_ids = array_keys($product_link_ids);
                $tier_prices_data = $this->fetch_tier_prices($product_link_ids);
                $export_data = $this->prepare_export_data($products_by_stores, $tier_prices_data);
                if (!empty($export_data)) {
                    asort($export_data);
                }
            }
        } catch (\Throwable $e) {
            $this->_logger->critical($e);
        }
        return $export_data;
    }
    /**
     * Creating export-formatted row from tier price.
     *
     * @param array $tierPriceData Tier price information.
     *
     * @return array Formatted for export tier price information.
     */
    private function create_export_row(array $tier_price_data): array
    {
        //List of columns to display in export row.
        $export_row = $this->template_export_data;
        foreach (array_keys($export_row) as $key_template) {
            if (array_key_exists($key_template, $tier_price_data)) {
                if (in_array($key_template, $this->_price_website)) {
                    //If it's website column then getting website code.
                    $export_row[$key_template] = $this->_get_website_code($tier_price_data[$key_template]);
                } elseif (in_array($key_template, $this->_price_customer_group)) {
                    //If it's customer group column then getting customer
                    //group name by ID.
                    $export_row[$key_template] = $this->_get_customer_group_by_id($tier_price_data[$key_template], $tier_price_data[Import_Advanced_Pricing::VALUE_ALL_GROUPS]);
                    unset($export_row[Import_Advanced_Pricing::VALUE_ALL_GROUPS]);
                } elseif ($key_template === Import_Advanced_Pricing::COL_TIER_PRICE) {
                    //If it's price column then getting value and type
                    //of tier price.
                    $export_row[$key_template] = $tier_price_data[Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE] ?: $tier_price_data[Import_Advanced_Pricing::COL_TIER_PRICE];
                    $export_row[Import_Advanced_Pricing::COL_TIER_PRICE_TYPE] = $this->tier_price_type_value($tier_price_data);
                } else {
                    //Any other column just goes as is.
                    $export_row[$key_template] = $tier_price_data[$key_template];
                }
            }
        }
        return $export_row;
    }
    /**
     * Prepare data for export.
     *
     * @param array $productsData Products to export.
     * @param array $tierPricesData Their tier prices.
     *
     * @return array Export rows to display.
     */
    private function prepare_export_data(array $products_data, array $tier_prices_data): array
    {
        //Assigning SKUs to tier prices data.
        $product_link_id_to_sku_map = [];
        foreach ($products_data as $product_data) {
            $product_link_id_to_sku_map[$product_data[Store::DEFAULT_STORE_ID][$this->get_product_entity_link_field()]] = $product_data[Store::DEFAULT_STORE_ID]['sku'];
        }
        //Adding products' SKUs to tier price data.
        $linked_tier_prices_data = [];
        foreach ($tier_prices_data as $tier_price_data) {
            $sku = $product_link_id_to_sku_map[$tier_price_data['product_link_id']];
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $linked_tier_prices_data[] = array_merge($tier_price_data, [Import_Advanced_Pricing::COL_SKU => $sku]);
        }
        //Formatting data for export.
        $custom_export_data = [];
        foreach ($linked_tier_prices_data as $row) {
            $custom_export_data[] = $this->create_export_row($row);
        }
        return $custom_export_data;
    }
    /**
     * Correct export data.
     *
     * @param array $exportData
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @deprecated 100.3.0
     * @see prepareExportData
     */
    protected function correct_export_data($export_data): array
    {
        $custom_export_data = [];
        foreach ($export_data as $key => $row) {
            $export_row = $this->template_export_data;
            foreach ($export_row as $key_template => $value_template) {
                if (isset($row[$key_template])) {
                    if (in_array($key_template, $this->_price_website)) {
                        $export_row[$key_template] = $this->_get_website_code($row[$key_template]);
                    } elseif (in_array($key_template, $this->_price_customer_group)) {
                        $export_row[$key_template] = $this->_get_customer_group_by_id($row[$key_template], $row[Import_Advanced_Pricing::VALUE_ALL_GROUPS] ?? null);
                        unset($export_row[Import_Advanced_Pricing::VALUE_ALL_GROUPS]);
                    } elseif ($key_template === Import_Advanced_Pricing::COL_TIER_PRICE) {
                        $export_row[$key_template] = $row[Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE] ?: $row[Import_Advanced_Pricing::COL_TIER_PRICE];
                        $export_row[Import_Advanced_Pricing::COL_TIER_PRICE_TYPE] = $this->tier_price_type_value($row[Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE]);
                    } else {
                        $export_row[$key_template] = $row[$key_template];
                    }
                }
            }
            $custom_export_data[$key] = $export_row;
            unset($export_row);
        }
        return $custom_export_data;
    }
    /**
     * Check type for tier price.
     */
    private function tier_price_type_value(array $tier_price_data): string
    {
        return $tier_price_data[Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE] ? Import_Advanced_Pricing::TIER_PRICE_TYPE_PERCENT : Import_Advanced_Pricing::TIER_PRICE_TYPE_FIXED;
    }
    /**
     * Load tier prices for given products.
     *
     * @param string[] $productIds Link IDs of products to find tier prices for.
     *
     * @return array Tier prices data.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function fetch_tier_prices(array $product_ids): array
    {
        if (empty($product_ids)) {
            throw new \InvalidArgumentException('Can only load tier prices for specific products');
        }
        $prices_table = Import_Advanced_Pricing::TABLE_TIER_PRICE;
        $export_filter = null;
        $price_from_filter = null;
        $price_to_filter = null;
        if (isset($this->_parameters[Export::FILTER_ELEMENT_GROUP])) {
            $export_filter = $this->_parameters[Export::FILTER_ELEMENT_GROUP];
        }
        $product_entity_link_field = $this->get_product_entity_link_field();
        $select_fields = [Import_Advanced_Pricing::COL_TIER_PRICE_WEBSITE => 'ap.website_id', Import_Advanced_Pricing::VALUE_ALL_GROUPS => 'ap.all_groups', Import_Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'ap.customer_group_id', Import_Advanced_Pricing::COL_TIER_PRICE_QTY => 'ap.qty', Import_Advanced_Pricing::COL_TIER_PRICE => 'ap.value', Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE => 'ap.percentage_value', 'product_link_id' => 'ap.' . $product_entity_link_field];
        if ($export_filter && array_key_exists('tier_price', $export_filter)) {
            if (!empty($export_filter['tier_price'][0])) {
                $price_from_filter = $export_filter['tier_price'][0];
            }
            if (!empty($export_filter['tier_price'][1])) {
                $price_to_filter = $export_filter['tier_price'][1];
            }
        }
        $select = $this->_connection->select()->from(['ap' => $this->_resource->get_table_name($prices_table)], $select_fields)->where('ap.' . $product_entity_link_field . ' IN (?)', $product_ids, \Zend_Db::INT_TYPE);
        if ($price_from_filter !== null) {
            $select->where('ap.value >= ?', $price_from_filter);
        }
        if ($price_to_filter !== null) {
            $select->where('ap.value <= ?', $price_to_filter);
        }
        if ($price_from_filter || $price_to_filter) {
            $select->or_where('ap.percentage_value IS NOT NULL');
        }
        return $this->_connection->fetch_all($select);
    }
    /**
     * Get tier prices.
     *
     * @param string $table
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated 100.3.0
     * @see fetchTierPrices
     */
    protected function get_tier_prices(array $list_sku, $table)
    {
        if (isset($this->_parameters[\Magento\Import_Export\Model\Export::FILTER_ELEMENT_GROUP])) {
            $export_filter = $this->_parameters[\Magento\Import_Export\Model\Export::FILTER_ELEMENT_GROUP];
        }
        $select_fields = [];
        $export_data = false;
        if ($table == Import_Advanced_Pricing::TABLE_TIER_PRICE) {
            $select_fields = [Import_Advanced_Pricing::COL_SKU => 'cpe.sku', Import_Advanced_Pricing::COL_TIER_PRICE_WEBSITE => 'ap.website_id', Import_Advanced_Pricing::VALUE_ALL_GROUPS => 'ap.all_groups', Import_Advanced_Pricing::COL_TIER_PRICE_CUSTOMER_GROUP => 'ap.customer_group_id', Import_Advanced_Pricing::COL_TIER_PRICE_QTY => 'ap.qty', Import_Advanced_Pricing::COL_TIER_PRICE => 'ap.value', Import_Advanced_Pricing::COL_TIER_PRICE_PERCENTAGE_VALUE => 'ap.percentage_value'];
            if (isset($export_filter) && !empty($export_filter)) {
                $price = $export_filter['tier_price'];
            }
        }
        if ($list_sku) {
            if (isset($export_filter) && !empty($export_filter)) {
                $date = $export_filter[\Magento\Catalog\Model\Category::KEY_UPDATED_AT];
                if (isset($date[0]) && !empty($date[0])) {
                    $updated_at_from = $this->_locale_date->date($date[0], null, false)->format('Y-m-d H:i:s');
                }
                if (isset($date[1]) && !empty($date[1])) {
                    $updated_at_to = $this->_locale_date->date($date[1], null, false)->format('Y-m-d H:i:s');
                }
            }
            try {
                $product_entity_link_field = $this->get_product_entity_link_field();
                $select = $this->_connection->select()->from(['cpe' => $this->_resource->get_table_name('catalog_product_entity')], $select_fields)->join_inner(['ap' => $this->_resource->get_table_name($table)], 'ap.' . $product_entity_link_field . ' = cpe.' . $product_entity_link_field, [])->where('cpe.entity_id IN (?)', $list_sku);
                if (isset($price[0]) && !empty($price[0])) {
                    $select->where('ap.value >= ?', $price[0]);
                }
                if (isset($price[1]) && !empty($price[1])) {
                    $select->where('ap.value <= ?', $price[1]);
                }
                if (isset($price[0]) && !empty($price[0]) || isset($price[1]) && !empty($price[1])) {
                    $select->or_where('ap.percentage_value IS NOT NULL');
                }
                if (isset($updated_at_from)) {
                    $select->where('cpe.updated_at >= ?', $updated_at_from);
                }
                if (isset($updated_at_to)) {
                    $select->where('cpe.updated_at <= ?', $updated_at_to);
                }
                $export_data = $this->_connection->fetch_all($select);
            } catch (\Exception) {
                return false;
            }
        }
        return $export_data;
    }
    /**
     * Get Website code.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _get_website_code(int $website_id): string
    {
        if (!array_key_exists($website_id, $this->website_codes_map)) {
            $store_name = $website_id == 0 ? Import_Advanced_Pricing::VALUE_ALL_WEBSITES : $this->_store_manager->get_website($website_id)->get_code();
            $currency_code = '';
            if ($website_id == 0) {
                $currency_code = $this->_store_manager->get_website($website_id)->get_base_currency_code();
            }
            if ($store_name && $currency_code) {
                $code = $store_name . ' [' . $currency_code . ']';
            } else {
                $code = $store_name;
            }
            $this->website_codes_map[$website_id] = $code;
        }
        return $this->website_codes_map[$website_id];
    }
    /**
     * Get Customer Group By Id.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _get_customer_group_by_id(int $group_id, int $all_groups = 0): string
    {
        if ($all_groups !== 0) {
            return Import_Advanced_Pricing::VALUE_ALL_GROUPS;
        }
        return $this->_group_repository->get_by_id($group_id)->get_code();
    }
    /**
     * Get Entity type code
     */
    public function get_entity_type_code(): string
    {
        if (!$this->_entity_type_code) {
            $this->_entity_type_code = Catalog_Product::ENTITY;
        } else {
            $this->_entity_type_code = self::ENTITY_ADVANCED_PRICING;
        }
        return $this->_entity_type_code;
    }
}