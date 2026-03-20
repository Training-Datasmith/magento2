<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Pricing_Import_Export\Model\Import;

use Magento\Advanced_Pricing_Import_Export\Model\Currency_Resolver;
use Magento\Catalog_Import_Export\Model\Import\Product\Row_Validator_Interface as ValidatorInterface;
use Magento\Framework\App\Object_Manager;
use Magento\Import_Export\Model\Import\Error_Processing\Processing_Error_Aggregator_Interface;
/**
 *  Import advanced pricing class
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Advanced_Pricing extends \Magento\Import_Export\Model\Import\Entity\Abstract_Entity
{
    public const VALUE_ALL_GROUPS = 'ALL GROUPS';
    public const VALUE_ALL_WEBSITES = 'All Websites';
    public const COL_SKU = 'sku';
    public const COL_TIER_PRICE_WEBSITE = 'tier_price_website';
    public const COL_TIER_PRICE_CUSTOMER_GROUP = 'tier_price_customer_group';
    public const COL_TIER_PRICE_QTY = 'tier_price_qty';
    public const COL_TIER_PRICE = 'tier_price';
    public const COL_TIER_PRICE_PERCENTAGE_VALUE = 'percentage_value';
    public const COL_TIER_PRICE_TYPE = 'tier_price_value_type';
    public const TIER_PRICE_TYPE_FIXED = 'Fixed';
    public const TIER_PRICE_TYPE_PERCENT = 'Discount';
    public const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';
    public const DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE = '0';
    public const ENTITY_TYPE_CODE = 'advanced_pricing';
    public const VALIDATOR_MAIN = 'validator';
    public const VALIDATOR_WEBSITE = 'validator_website';
    private const VALIDATOR_TIER_PRICE = 'validator_tier_price';
    private const ERROR_DUPLICATE_TIER_PRICE = 'duplicateTierPrice';
    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_message_templates = [Validator_Interface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)', Validator_Interface::ERROR_SKU_IS_EMPTY => 'SKU is empty', Validator_Interface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found', Validator_Interface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid', Validator_Interface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid', Validator_Interface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group is invalid', Validator_Interface::ERROR_INVALID_TIER_PRICE_TYPE => 'Value for \'tier_price_value_type\' ' . 'attribute contains incorrect value, acceptable values are Fixed, Discount', Validator_Interface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete', Validator_Interface::ERROR_INVALID_ATTRIBUTE_DECIMAL => 'Value for \'%s\' attribute contains incorrect value,' . ' acceptable values are in decimal format', self::ERROR_DUPLICATE_TIER_PRICE => 'We found a duplicate website, tier price, customer group' . ' and quantity.'];
    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $need_column_check = true;
    /**
     * @var array
     */
    protected $valid_column_names = [self::COL_SKU, self::COL_TIER_PRICE_WEBSITE, self::COL_TIER_PRICE_CUSTOMER_GROUP, self::COL_TIER_PRICE_QTY, self::COL_TIER_PRICE, self::COL_TIER_PRICE_TYPE];
    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $log_in_history = true;
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resource_factory;
    /**
     * @var array
     */
    protected $_validators = [];
    /**
     * @var array
     */
    protected $_cached_sku_to_delete;
    /**
     * @var array
     */
    protected $_old_skus;
    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanent_attributes = [self::COL_SKU];
    /**
     * @var string
     */
    protected $_catalog_product_entity;
    /**
     * @var string
     */
    private $product_entity_link_field;
    private array $website_scope_tier_price = [];
    private array $global_scope_tier_price = [];
    private array $all_product_ids = [];
    /**
     * @var CurrencyResolver
     */
    private $currency_resolver;
    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Magento\Framework\Json\Helper\Data $json_helper, \Magento\Import_Export\Helper\Data $import_export_data, \Magento\Import_Export\Model\Resource_Model\Import\Data $import_data, \Magento\Framework\App\Resource_Connection $resource, \Magento\Import_Export\Model\Resource_Model\Helper $resource_helper, Processing_Error_Aggregator_Interface $error_aggregator, protected \Magento\Framework\Stdlib\DateTime\DateTime $date_time, \Magento\Catalog_Import_Export\Model\Import\Proxy\Product\Resource_Model_Factory $resource_factory, protected \Magento\Catalog\Model\Product $_product_model, protected \Magento\Catalog\Helper\Data $_catalog_data, protected \Magento\Catalog_Import_Export\Model\Import\Product\Store_Resolver $_store_resolver, protected \Magento\Catalog_Import_Export\Model\Import\Product $_import_product, Advanced_Pricing\Validator $validator, Advanced_Pricing\Validator\Website $website_validator, Advanced_Pricing\Validator\Tier_Price $tier_price_validator, ?Currency_Resolver $currency_resolver = null)
    {
        $this->json_helper = $json_helper;
        $this->_import_export_data = $import_export_data;
        $this->_resource_helper = $resource_helper;
        $this->_data_source_model = $import_data;
        $this->_connection = $resource->get_connection('write');
        $this->_resource_factory = $resource_factory;
        $this->_validators[self::VALIDATOR_MAIN] = $validator->init($this);
        $this->_catalog_product_entity = $this->_resource_factory->create()->get_table('catalog_product_entity');
        $this->_old_skus = $this->retrieve_old_skus();
        $this->_validators[self::VALIDATOR_WEBSITE] = $website_validator;
        $this->_validators[self::VALIDATOR_TIER_PRICE] = $tier_price_validator;
        $this->error_aggregator = $error_aggregator;
        $this->currency_resolver = $currency_resolver ?? Object_Manager::get_instance()->get(Currency_Resolver::class);
        foreach (array_merge($this->error_message_templates, $this->_message_templates) as $error_code => $message) {
            $this->get_error_aggregator()->add_error_message_template($error_code, $message);
        }
    }
    /**
     * Validator object getter.
     *
     * @param string $type
     * @return AdvancedPricing\Validator|AdvancedPricing\Validator\Website
     */
    protected function _get_validator($type)
    {
        return $this->_validators[$type];
    }
    /**
     * Entity type code getter.
     */
    public function get_entity_type_code(): string
    {
        return 'advanced_pricing';
    }
    /**
     * Row validation.
     *
     * @param int $rowNum
     * @return bool
     */
    public function validate_row(array $row_data, $row_num)
    {
        $sku = false;
        if (isset($this->_validated_rows[$row_num])) {
            return !$this->get_error_aggregator()->is_row_invalid($row_num);
        }
        $this->_validated_rows[$row_num] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\Import_Export\Model\Import::BEHAVIOR_DELETE == $this->get_behavior()) {
            if (!isset($row_data[self::COL_SKU])) {
                $this->add_row_error(Validator_Interface::ERROR_SKU_IS_EMPTY, $row_num);
                return false;
            }
            return true;
        }
        if (!$this->_get_validator(self::VALIDATOR_MAIN)->is_valid($row_data)) {
            foreach ($this->_get_validator(self::VALIDATOR_MAIN)->get_messages() as $message) {
                $this->add_row_error($message, $row_num);
            }
        }
        if (isset($row_data[self::COL_SKU])) {
            $sku = $row_data[self::COL_SKU];
        }
        if (false === $sku) {
            $this->add_row_error(Validator_Interface::ERROR_ROW_IS_ORPHAN, $row_num);
        }
        if (!$this->get_error_aggregator()->is_row_invalid($row_num)) {
            $this->validate_row_for_duplicate($row_data, $row_num);
        }
        return !$this->get_error_aggregator()->is_row_invalid($row_num);
    }
    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _import_data(): bool
    {
        if (\Magento\Import_Export\Model\Import::BEHAVIOR_DELETE == $this->get_behavior()) {
            $this->delete_advanced_pricing();
        } elseif (\Magento\Import_Export\Model\Import::BEHAVIOR_REPLACE == $this->get_behavior()) {
            $this->replace_advanced_pricing();
        } elseif (\Magento\Import_Export\Model\Import::BEHAVIOR_APPEND == $this->get_behavior()) {
            $this->save_advanced_pricing();
        }
        return true;
    }
    /**
     * Save advanced pricing
     *
     * @return $this
     * @throws \Exception
     */
    public function save_advanced_pricing(): static
    {
        $this->save_and_replace_advanced_prices();
        return $this;
    }
    /**
     * Deletes Advanced price data from raw data.
     *
     * @return $this
     * @throws \Exception
     */
    public function delete_advanced_pricing(): static
    {
        $this->_cached_sku_to_delete = null;
        $list_sku = [];
        while ($bunch = $this->_data_source_model->get_next_unique_bunch($this->get_ids())) {
            foreach ($bunch as $row_num => $row_data) {
                $this->validate_row($row_data, $row_num);
                if (!$this->get_error_aggregator()->is_row_invalid($row_num)) {
                    $row_sku = $row_data[self::COL_SKU];
                    $list_sku[] = $row_sku;
                }
                if ($this->get_error_aggregator()->has_to_be_terminated()) {
                    $this->get_error_aggregator()->add_row_to_skip($row_num);
                }
            }
        }
        if ($list_sku) {
            $this->delete_product_tier_prices(array_unique($list_sku), self::TABLE_TIER_PRICE);
            $this->set_updated_at($list_sku);
        }
        return $this;
    }
    /**
     * Replace advanced pricing
     *
     * @return $this
     * @throws \Exception
     */
    public function replace_advanced_pricing(): static
    {
        $this->save_and_replace_advanced_prices();
        return $this;
    }
    /**
     * Save and replace advanced prices
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function save_and_replace_advanced_prices(): static
    {
        $behavior = $this->get_behavior();
        if (\Magento\Import_Export\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cached_sku_to_delete = null;
        }
        $list_sku = [];
        $tier_prices = [];
        while ($bunch = $this->_data_source_model->get_next_unique_bunch($this->get_ids())) {
            $bunch_tier_prices = [];
            foreach ($bunch as $row_num => $row_data) {
                if (!$this->validate_row($row_data, $row_num)) {
                    $this->add_row_error(Validator_Interface::ERROR_SKU_IS_EMPTY, $row_num);
                    continue;
                }
                if ($this->get_error_aggregator()->has_to_be_terminated()) {
                    $this->get_error_aggregator()->add_row_to_skip($row_num);
                    continue;
                }
                $row_sku = $row_data[self::COL_SKU];
                $list_sku[] = $row_sku;
                if (!empty($row_data[self::COL_TIER_PRICE_WEBSITE])) {
                    $tier_price = ['all_groups' => $row_data[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS, 'customer_group_id' => $this->get_customer_group_id($row_data[self::COL_TIER_PRICE_CUSTOMER_GROUP]), 'qty' => $row_data[self::COL_TIER_PRICE_QTY], 'value' => $row_data[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_FIXED ? $row_data[self::COL_TIER_PRICE] : 0, 'percentage_value' => $row_data[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_PERCENT ? $row_data[self::COL_TIER_PRICE] : null, 'website_id' => $this->get_web_site_id($row_data[self::COL_TIER_PRICE_WEBSITE])];
                    if (\Magento\Import_Export\Model\Import::BEHAVIOR_APPEND == $behavior) {
                        $bunch_tier_prices[$row_sku][] = $tier_price;
                    }
                    if (\Magento\Import_Export\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                        $tier_prices[$row_sku][] = $tier_price;
                    }
                }
            }
            if (\Magento\Import_Export\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->process_count_existing_prices($bunch_tier_prices, self::TABLE_TIER_PRICE)->process_count_new_prices($bunch_tier_prices);
                $this->save_product_prices($bunch_tier_prices, self::TABLE_TIER_PRICE);
            }
        }
        if (\Magento\Import_Export\Model\Import::BEHAVIOR_APPEND == $behavior) {
            if ($list_sku) {
                $this->set_updated_at($list_sku);
            }
        } elseif (\Magento\Import_Export\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            if ($list_sku) {
                $this->process_count_new_prices($tier_prices);
                if ($this->delete_product_tier_prices(array_unique($list_sku), self::TABLE_TIER_PRICE)) {
                    $this->save_product_prices($tier_prices, self::TABLE_TIER_PRICE);
                    $this->set_updated_at($list_sku);
                }
            }
        }
        $this->finalize_count();
        return $this;
    }
    /**
     * Save product prices.
     *
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    protected function save_product_prices(array $price_data, $table): static
    {
        if ($price_data) {
            $table_name = $this->_resource_factory->create()->get_table($table);
            $price_in = [];
            $entity_ids = [];
            $old_skus = $this->retrieve_old_skus();
            foreach ($price_data as $sku => $price_rows) {
                if (isset($old_skus[$sku])) {
                    $product_id = $old_skus[$sku];
                    foreach ($price_rows as $row) {
                        $row[$this->get_product_entity_link_field()] = $product_id;
                        $price_in[] = $row;
                        $entity_ids[] = $product_id;
                    }
                }
            }
            if ($price_in) {
                $this->_connection->insert_on_duplicate($table_name, $price_in, ['value', 'percentage_value']);
            }
        }
        return $this;
    }
    /**
     * Deletes tier prices prices.
     *
     * @param string $table
     * @throws \Exception
     */
    protected function delete_product_tier_prices(array $list_sku, $table): bool
    {
        $table_name = $this->_resource_factory->create()->get_table($table);
        $product_entity_link_field = $this->get_product_entity_link_field();
        if ($table_name && $list_sku) {
            if (!$this->_cached_sku_to_delete) {
                $this->_cached_sku_to_delete = $this->_connection->fetch_col($this->_connection->select()->from($this->_catalog_product_entity, $product_entity_link_field)->where('sku IN (?)', $list_sku));
            }
            if ($this->_cached_sku_to_delete) {
                try {
                    $this->count_items_deleted += $this->_connection->delete($table_name, $this->_connection->quote_into($product_entity_link_field . ' IN (?)', $this->_cached_sku_to_delete));
                    return true;
                } catch (\Exception) {
                    return false;
                }
            } else {
                $this->add_row_error(Validator_Interface::ERROR_SKU_IS_EMPTY, 0);
                return false;
            }
        }
        return false;
    }
    /**
     * Set updated_at for product
     *
     * @return $this
     */
    protected function set_updated_at(array $list_sku): static
    {
        $updated_at = $this->date_time->gmt_date('Y-m-d H:i:s');
        $this->_connection->update($this->_catalog_product_entity, [\Magento\Catalog\Model\Category::KEY_UPDATED_AT => $updated_at], $this->_connection->quote_into('sku IN (?)', array_unique($list_sku)));
        return $this;
    }
    /**
     * Get website id by code
     *
     * @param string $websiteCode
     * @return array|int|string
     */
    protected function get_web_site_id($website_code)
    {
        return $website_code == $this->_get_validator(self::VALIDATOR_WEBSITE)->get_all_websites_value() || $this->_catalog_data->is_price_global() ? 0 : $this->_store_resolver->get_website_code_to_id($website_code);
    }
    /**
     * Get customer group id
     *
     * @param string $customerGroup
     * @return int
     */
    protected function get_customer_group_id($customer_group)
    {
        $customer_groups = $this->_get_validator(self::VALIDATOR_TIER_PRICE)->get_customer_groups();
        return $customer_group == self::VALUE_ALL_GROUPS ? 0 : $customer_groups[$customer_group];
    }
    /**
     * Retrieve product skus
     *
     * @return array
     * @throws \Exception
     */
    protected function retrieve_old_skus()
    {
        if ($this->_old_skus === null) {
            $this->_old_skus = $this->_connection->fetch_pairs($this->_connection->select()->from($this->_catalog_product_entity, ['sku', $this->get_product_entity_link_field()]));
        }
        return $this->_old_skus;
    }
    /**
     * Count existing prices
     *
     * @param array $prices
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    protected function process_count_existing_prices($prices, $table): static
    {
        $old_skus = $this->retrieve_old_skus();
        $exist_product_ids = array_intersect_key($old_skus, $prices);
        if (!count($exist_product_ids)) {
            return $this;
        }
        $table_name = $this->_resource_factory->create()->get_table($table);
        $product_entity_link_field = $this->get_product_entity_link_field();
        $existing_prices = $this->_connection->fetch_all($this->_connection->select()->from($table_name, [$product_entity_link_field, 'all_groups', 'customer_group_id', 'qty'])->where($product_entity_link_field . ' IN (?)', $exist_product_ids));
        foreach ($existing_prices as $existing_price) {
            foreach ($prices as $sku => $sku_prices) {
                if (isset($old_skus[$sku]) && $existing_price[$product_entity_link_field] == $old_skus[$sku]) {
                    $this->increment_counter_updated($sku_prices, $existing_price);
                }
            }
        }
        return $this;
    }
    /**
     * Increment counter of updated items
     *
     * @param array $prices
     * @return void
     */
    protected function increment_counter_updated($prices, array $existing_price)
    {
        foreach ($prices as $price) {
            if ($existing_price['all_groups'] == $price['all_groups'] && $existing_price['customer_group_id'] == $price['customer_group_id'] && (int) $existing_price['qty'] === (int) $price['qty']) {
                $this->count_items_updated++;
                continue;
            }
        }
    }
    /**
     * Count new prices
     *
     * @return $this
     */
    protected function process_count_new_prices(array $tier_prices): static
    {
        foreach ($tier_prices as $product_prices) {
            $this->count_items_created += count($product_prices);
        }
        return $this;
    }
    /**
     *  Finalize count of new and existing records
     */
    protected function finalize_count()
    {
        $this->count_items_created -= $this->count_items_updated;
    }
    /**
     * Get product entity link field
     *
     * @return string
     * @throws \Exception
     */
    private function get_product_entity_link_field()
    {
        if (!$this->product_entity_link_field) {
            $this->product_entity_link_field = $this->get_metadata_pool()->get_metadata(\Magento\Catalog\Api\Data\Product_Interface::class)->get_link_field();
        }
        return $this->product_entity_link_field;
    }
    /**
     * @inheritdoc
     */
    protected function _save_validated_bunches()
    {
        if (\Magento\Import_Export\Model\Import::BEHAVIOR_APPEND === $this->get_behavior() && !$this->_catalog_data->is_price_global()) {
            $source = $this->_get_source();
            $source->rewind();
            while ($source->valid()) {
                try {
                    $row_data = $source->current();
                } catch (\InvalidArgumentException) {
                    $source->next();
                    continue;
                }
                $this->validate_row($row_data, $source->key());
                $source->next();
            }
            $this->validate_rows_for_duplicate(self::TABLE_TIER_PRICE);
        }
        return parent::_save_validated_bunches();
    }
    /**
     * Validate all row data with existing prices in the database for duplicate
     *
     * A row is considered a duplicate if the pair (product_id, all_groups, customer_group_id, qty) exists for
     * both global and website scopes. And the base currency is the same for both global and website scopes.
     */
    private function validate_rows_for_duplicate(string $table): void
    {
        if (!empty($this->all_product_ids)) {
            $price_data_collection = $this->get_prices(array_keys($this->all_product_ids), $table);
            $default_base_currency = $this->currency_resolver->get_default_base_currency();
            $website_code_base_currency_map = $this->currency_resolver->get_websites_base_currency();
            $website_id_code_map = array_flip($this->_store_resolver->get_website_code_to_id());
            foreach ($price_data_collection as $price_data) {
                $is_default_scope = (int) $price_data['website_id'] === 0;
                $base_currency = $is_default_scope ? $default_base_currency : $website_code_base_currency_map[$website_id_code_map[$price_data['website_id']] ?? null] ?? null;
                $row_nums = [];
                $key = $this->get_unique_key($price_data, $base_currency);
                if ($is_default_scope) {
                    if (isset($this->website_scope_tier_price[$key])) {
                        $row_nums = $this->website_scope_tier_price[$key];
                    }
                } else if (isset($this->global_scope_tier_price[$key])) {
                    $row_nums = $this->global_scope_tier_price[$key];
                }
                foreach ($row_nums as $row_num) {
                    $this->add_row_error(self::ERROR_DUPLICATE_TIER_PRICE, $row_num);
                }
            }
        }
    }
    /**
     * Validate row data for duplicate
     *
     * A row is considered a duplicate if the pair (product_id, all_groups, customer_group_id, qty) exists for
     * both global and website scopes. And the base currency is the same for both global and website scopes.
     */
    private function validate_row_for_duplicate(array $row_data, int $row_num): void
    {
        $product_id = $this->retrieve_old_skus()[$row_data[self::COL_SKU]] ?? null;
        if ($product_id && !$this->_catalog_data->is_price_global()) {
            $product_entity_link_field = $this->get_product_entity_link_field();
            $price_data = [$product_entity_link_field => $product_id, 'website_id' => (int) $this->get_web_site_id($row_data[self::COL_TIER_PRICE_WEBSITE]), 'all_groups' => $row_data[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS ? 1 : 0, 'customer_group_id' => $this->get_customer_group_id($row_data[self::COL_TIER_PRICE_CUSTOMER_GROUP]), 'qty' => $row_data[self::COL_TIER_PRICE_QTY]];
            $default_base_currency = $this->currency_resolver->get_default_base_currency();
            $website_code_base_currency_map = $this->currency_resolver->get_websites_base_currency();
            $website_id_code_map = array_flip($this->_store_resolver->get_website_code_to_id());
            $base_currency = $price_data['website_id'] === 0 ? $default_base_currency : $website_code_base_currency_map[$website_id_code_map[$price_data['website_id']] ?? null] ?? null;
            $this->all_product_ids[$product_id][] = $row_num;
            $key = $this->get_unique_key($price_data, $base_currency);
            if ($price_data['website_id'] === 0) {
                $this->global_scope_tier_price[$key][] = $row_num;
                if (isset($this->website_scope_tier_price[$key])) {
                    $this->add_row_error(self::ERROR_DUPLICATE_TIER_PRICE, $row_num);
                }
            } else {
                $this->website_scope_tier_price[$key][] = $row_num;
                if (isset($this->global_scope_tier_price[$key])) {
                    $this->add_row_error(self::ERROR_DUPLICATE_TIER_PRICE, $row_num);
                }
            }
        }
    }
    /**
     * Get the unique key of provided price
     */
    private function get_unique_key(array $price_data, string $base_currency): string
    {
        $product_entity_link_field = $this->get_product_entity_link_field();
        return sprintf('%s-%s-%s-%s-%.4f', $base_currency, $price_data[$product_entity_link_field], $price_data['all_groups'], $price_data['customer_group_id'], $price_data['qty']);
    }
    /**
     * Get existing prices in the database
     *
     * @param int[] $productIds
     * @return array
     */
    private function get_prices(array $product_ids, string $table)
    {
        $product_entity_link_field = $this->get_product_entity_link_field();
        return $this->_connection->fetch_all($this->_connection->select()->from($this->_resource_factory->create()->get_table($table), [$product_entity_link_field, 'all_groups', 'customer_group_id', 'qty', 'website_id'])->where($product_entity_link_field . ' IN (?)', $product_ids));
    }
}