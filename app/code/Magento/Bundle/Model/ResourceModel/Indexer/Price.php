<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Catalog\Api\Data\Product_Interface;
use Magento\Catalog\Model\Indexer\Product\Price\Table_Maintainer;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Resource_Model\Product\Indexer\Price\Base_Price_Modifier;
use Magento\Catalog\Model\Resource_Model\Product\Indexer\Price\Index_Table_Structure;
use Magento\Catalog\Model\Resource_Model\Product\Indexer\Price\Index_Table_Structure_Factory;
use Magento\Catalog\Model\Resource_Model\Product\Indexer\Price\Query\Join_Attribute_Processor;
use Magento\Customer\Model\Indexer\Customer_Group_Dimension_Provider;
use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Select;
use Magento\Framework\Entity_Manager\Metadata_Pool;
use Magento\Framework\Event\Manager_Interface;
use Magento\Framework\Indexer\Dimensional_Indexer_Interface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\Indexer\Website_Dimension_Provider;
/**
 * Bundle products Price indexer resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Price implements Dimensional_Indexer_Interface
{
    /**
     * @var IndexTableStructureFactory
     */
    private $index_table_structure_factory;
    /**
     * @var TableMaintainer
     */
    private $table_maintainer;
    /**
     * @var MetadataPool
     */
    private $metadata_pool;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var bool
     */
    private $full_reindex_action;
    /**
     * @var string
     */
    private $connection_name;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * Mapping between dimensions and field in database
     *
     * @var array
     */
    private $dimension_to_field_mapper = [Website_Dimension_Provider::DIMENSION_NAME => 'pw.website_id', Customer_Group_Dimension_Provider::DIMENSION_NAME => 'cg.customer_group_id'];
    /**
     * @var BasePriceModifier
     */
    private $base_price_modifier;
    /**
     * @var JoinAttributeProcessor
     */
    private $join_attribute_processor;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $event_manager;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $module_manager;
    /**
     * @var string
     */
    private $tmp_bundle_price_table;
    /**
     * @var string
     */
    private $tmp_bundle_selection_table;
    /**
     * @var string
     */
    private $tmp_bundle_option_table;
    /**
     * @var StockStatusQueryProcessorInterface
     */
    private Stock_Status_Query_Processor_Interface $stock_status_query_processor;
    /**
     * @var SelectionPriceModifierInterface
     */
    private Selection_Price_Modifier_Interface $selection_price_indexer;
    /**
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param ManagerInterface $eventManager
     * @param Manager $moduleManager
     * @param StockStatusQueryProcessorInterface|null $stockStatusQueryProcessor
     * @param bool $fullReindexAction
     * @param string $connectionName
     * @param SelectionPriceModifierInterface|null $selectionPriceIndexer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Index_Table_Structure_Factory $index_table_structure_factory, Table_Maintainer $table_maintainer, Metadata_Pool $metadata_pool, \Magento\Framework\App\Resource_Connection $resource, Base_Price_Modifier $base_price_modifier, Join_Attribute_Processor $join_attribute_processor, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\Module\Manager $module_manager, ?Stock_Status_Query_Processor_Interface $stock_status_query_processor = null, $full_reindex_action = false, $connection_name = 'indexer', ?Selection_Price_Modifier_Interface $selection_price_indexer = null)
    {
        $this->index_table_structure_factory = $index_table_structure_factory;
        $this->table_maintainer = $table_maintainer;
        $this->connection_name = $connection_name;
        $this->metadata_pool = $metadata_pool;
        $this->resource = $resource;
        $this->full_reindex_action = $full_reindex_action;
        $this->base_price_modifier = $base_price_modifier;
        $this->join_attribute_processor = $join_attribute_processor;
        $this->event_manager = $event_manager;
        $this->module_manager = $module_manager;
        $this->stock_status_query_processor = $stock_status_query_processor ?? \Magento\Framework\App\Object_Manager::get_instance()->get(Stock_Status_Query_Processor_Interface::class);
        $this->selection_price_indexer = $selection_price_indexer ?? \Magento\Framework\App\Object_Manager::get_instance()->get(Selection_Price_Modifier_Interface::class);
    }
    /**
     * @inheritdoc
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @throws \Exception
     */
    public function execute_by_dimensions(array $dimensions, \Traversable $entity_ids)
    {
        $this->table_maintainer->create_main_tmp_table($dimensions);
        $temporary_price_table = $this->index_table_structure_factory->create(['tableName' => $this->table_maintainer->get_main_tmp_table($dimensions), 'entityField' => 'entity_id', 'customerGroupField' => 'customer_group_id', 'websiteField' => 'website_id', 'taxClassField' => 'tax_class_id', 'originalPriceField' => 'price', 'finalPriceField' => 'final_price', 'minPriceField' => 'min_price', 'maxPriceField' => 'max_price', 'tierPriceField' => 'tier_price']);
        $entity_ids = iterator_to_array($entity_ids);
        $this->prepare_tier_price_index($dimensions, $entity_ids);
        $this->prepare_bundle_price_table();
        $this->prepare_bundle_price_by_type(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED, $dimensions, $entity_ids);
        $this->prepare_bundle_price_by_type(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC, $dimensions, $entity_ids);
        $this->calculate_bundle_option_price($temporary_price_table, $dimensions);
        $this->base_price_modifier->modify_price($temporary_price_table, $entity_ids);
    }
    /**
     * Retrieve temporary price index table name for fixed bundle products
     *
     * @return string
     */
    private function get_bundle_price_table()
    {
        if ($this->tmp_bundle_price_table === null) {
            $this->tmp_bundle_price_table = $this->get_table('catalog_product_index_price_bundle_temp');
            $this->get_connection()->create_temporary_table_like($this->tmp_bundle_price_table, $this->get_table('catalog_product_index_price_bundle_tmp'), true);
        }
        return $this->tmp_bundle_price_table;
    }
    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    private function get_bundle_selection_table()
    {
        if ($this->tmp_bundle_selection_table === null) {
            $this->tmp_bundle_selection_table = $this->get_table('catalog_product_index_price_bundle_sel_temp');
            $this->get_connection()->create_temporary_table_like($this->tmp_bundle_selection_table, $this->get_table('catalog_product_index_price_bundle_sel_tmp'), true);
        }
        return $this->tmp_bundle_selection_table;
    }
    /**
     * Retrieve table name for temporary bundle option prices index
     *
     * @return string
     */
    private function get_bundle_option_table()
    {
        if ($this->tmp_bundle_option_table === null) {
            $this->tmp_bundle_option_table = $this->get_table('catalog_product_index_price_bundle_opt_temp');
            $this->get_connection()->create_temporary_table_like($this->tmp_bundle_option_table, $this->get_table('catalog_product_index_price_bundle_opt_tmp'), true);
        }
        return $this->tmp_bundle_option_table;
    }
    /**
     * Prepare temporary price index table for fixed bundle products
     *
     * @return $this
     */
    private function prepare_bundle_price_table()
    {
        $this->get_connection()->delete($this->get_bundle_price_table());
        return $this;
    }
    /**
     * Prepare table structure for temporary bundle selection prices index
     *
     * @return $this
     */
    private function prepare_bundle_selection_table()
    {
        $this->get_connection()->delete($this->get_bundle_selection_table());
        return $this;
    }
    /**
     * Prepare table structure for temporary bundle option prices index
     *
     * @return $this
     */
    private function prepare_bundle_option_table()
    {
        $this->get_connection()->delete($this->get_bundle_option_table());
        return $this;
    }
    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param int $priceType
     * @param array $dimensions
     * @param int|array $entityIds the entity ids limitation
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function prepare_bundle_price_by_type($price_type, array $dimensions, $entity_ids = null)
    {
        $connection = $this->get_connection();
        $select = $connection->select()->from(['e' => $this->get_table('catalog_product_entity')], ['entity_id'])->join_inner(['cg' => $this->get_table('customer_group')], array_key_exists(Customer_Group_Dimension_Provider::DIMENSION_NAME, $dimensions) ? sprintf('%s = %s', $this->dimension_to_field_mapper[Customer_Group_Dimension_Provider::DIMENSION_NAME], $dimensions[Customer_Group_Dimension_Provider::DIMENSION_NAME]->get_value()) : '', ['customer_group_id'])->join_inner(['pw' => $this->get_table('catalog_product_website')], 'pw.product_id = e.entity_id', ['pw.website_id'])->join_inner(['cwd' => $this->get_table('catalog_product_index_website')], 'pw.website_id = cwd.website_id', [])->join_left(['cgw' => $this->get_table('customer_group_excluded_website')], 'cg.customer_group_id = cgw.customer_group_id AND pw.website_id = cgw.website_id', []);
        $select->join_left(['tp' => $this->get_table('catalog_product_index_tier_price')], 'tp.entity_id = e.entity_id AND tp.website_id = pw.website_id' . ' AND tp.customer_group_id = cg.customer_group_id', [])->where('e.type_id=?', \Magento\Bundle\Ui\Data_Provider\Product\Listing\Collector\Bundle_Price::PRODUCT_TYPE);
        foreach ($dimensions as $dimension) {
            if (!isset($this->dimension_to_field_mapper[$dimension->get_name()])) {
                throw new \LogicException('Provided dimension is not valid for Price indexer: ' . $dimension->get_name());
            }
            $select->where($this->dimension_to_field_mapper[$dimension->get_name()] . ' = ?', $dimension->get_value());
        }
        $this->join_attribute_processor->process($select, 'status', Status::STATUS_ENABLED);
        if ($this->module_manager->is_enabled('Magento_Tax')) {
            $tax_class_id = $this->join_attribute_processor->process($select, 'tax_class_id');
        } else {
            $tax_class_id = new \Zend_Db_Expr('0');
        }
        if ($price_type == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(['tax_class_id' => $connection->get_check_sql($tax_class_id . ' IS NOT NULL', $tax_class_id, 0)]);
        }
        $this->join_attribute_processor->process($select, 'price_type', $price_type);
        $price = $this->join_attribute_processor->process($select, 'price');
        $special_price = $this->join_attribute_processor->process($select, 'special_price');
        $special_from = $this->join_attribute_processor->process($select, 'special_from_date');
        $special_to = $this->join_attribute_processor->process($select, 'special_to_date');
        $current_date = new \Zend_Db_Expr('cwd.website_date');
        $special_from_date = $connection->get_date_part_sql($special_from);
        $special_to_date = $connection->get_date_part_sql($special_to);
        $special_from_expr = "{$special_from} IS NULL OR {$special_from_date} <= {$current_date}";
        $special_to_expr = "{$special_to} IS NULL OR {$special_to_date} >= {$current_date}";
        $special_expr = "{$special_price} IS NOT NULL AND {$special_price} > 0 AND {$special_price} < 100" . " AND {$special_from_expr} AND {$special_to_expr}";
        $tier_expr = new \Zend_Db_Expr('tp.min_price');
        if ($price_type == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $special_price_expr = $connection->get_check_sql($special_expr, 'ROUND(' . $price . ' * (' . $special_price . '  / 100), 4)', 'NULL');
            $tier_price = $connection->get_check_sql($tier_expr . ' IS NOT NULL', 'ROUND((1 - ' . $tier_expr . ' / 100) * ' . $price . ', 4)', 'NULL');
            $final_price = $connection->get_least_sql([$price, $connection->get_if_null_sql($special_price_expr, $price), $connection->get_if_null_sql($tier_price, $price)]);
        } else {
            $final_price = new \Zend_Db_Expr('0');
            $tier_price = $connection->get_check_sql($tier_expr . ' IS NOT NULL', '0', 'NULL');
        }
        $select->columns(['price_type' => new \Zend_Db_Expr($price_type), 'special_price' => $connection->get_check_sql($special_expr, $special_price, '0'), 'tier_percent' => $tier_expr, 'orig_price' => $connection->get_if_null_sql($price, '0'), 'price' => $final_price, 'min_price' => $final_price, 'max_price' => $final_price, 'tier_price' => $tier_price, 'base_tier' => $tier_price]);
        if ($entity_ids !== null) {
            $select->where('e.entity_id IN(?)', $entity_ids);
        }
        // exclude websites that are limited for customer group
        $select->where('cgw.website_id IS NULL');
        /**
         * Add additional external limitation
         */
        $this->event_manager->dispatch('catalog_product_prepare_index_select', ['select' => $select, 'entity_field' => new \Zend_Db_Expr('e.entity_id'), 'website_field' => new \Zend_Db_Expr('pw.website_id'), 'store_field' => new \Zend_Db_Expr('cwd.default_store_id')]);
        $this->table_maintainer->insert_from_select($select, $this->get_bundle_price_table(), []);
    }
    /**
     * Calculate fixed bundle product selections price
     *
     * @param IndexTableStructure $priceTable
     * @param array $dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function calculate_bundle_option_price($price_table, $dimensions)
    {
        $connection = $this->get_connection();
        $this->prepare_bundle_selection_table();
        $this->calculate_fixed_bundle_selection_price();
        $this->calculate_dynamic_bundle_selection_price($dimensions);
        $this->selection_price_indexer->modify($this->get_bundle_selection_table(), $dimensions);
        $this->prepare_bundle_option_table();
        $select = $connection->select()->from($this->get_bundle_selection_table(), ['entity_id', 'customer_group_id', 'website_id', 'option_id'])->group(['entity_id', 'customer_group_id', 'website_id', 'option_id']);
        $min_price = $connection->get_check_sql('is_required = 1', 'price', 'NULL');
        $tier_price = $connection->get_check_sql('is_required = 1', 'tier_price', 'NULL');
        $select->columns(['min_price' => new \Zend_Db_Expr('MIN(' . $min_price . ')'), 'alt_price' => new \Zend_Db_Expr('MIN(price)'), 'max_price' => $connection->get_check_sql('group_type = 0', 'MAX(price)', 'SUM(price)'), 'tier_price' => new \Zend_Db_Expr('MIN(' . $tier_price . ')'), 'alt_tier_price' => new \Zend_Db_Expr('MIN(tier_price)')]);
        $this->table_maintainer->insert_from_select($select, $this->get_bundle_option_table(), []);
        $this->get_connection()->delete($price_table->get_table_name());
        $this->apply_bundle_price($price_table);
        $this->apply_bundle_option_price($price_table);
    }
    /**
     * Get base select for bundle selection price
     *
     * @return Select
     * @throws \Exception
     */
    private function get_base_bundle_selection_price_select(): Select
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $link_field = $metadata->get_link_field();
        $select = $this->get_connection()->select()->from(['i' => $this->get_bundle_price_table()], ['entity_id', 'customer_group_id', 'website_id'])->join(['parent_product' => $this->get_table('catalog_product_entity')], 'parent_product.entity_id = i.entity_id', [])->join(['bo' => $this->get_table('catalog_product_bundle_option')], "bo.parent_id = parent_product.{$link_field}", ['option_id'])->join(['bs' => $this->get_table('catalog_product_bundle_selection')], 'bs.option_id = bo.option_id', ['selection_id']);
        return $select;
    }
    /**
     * Get base select for bundle selection price update
     *
     * @return Select
     * @throws \Exception
     */
    private function get_base_bundle_selection_price_update_select(): Select
    {
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $link_field = $metadata->get_link_field();
        $bundle_selection_table = $this->get_bundle_selection_table();
        $select = $this->get_connection()->select()->join(['i' => $this->get_bundle_price_table()], "i.entity_id = {$bundle_selection_table}.entity_id\n             AND i.customer_group_id = {$bundle_selection_table}.customer_group_id\n             AND i.website_id = {$bundle_selection_table}.website_id", [])->join(['parent_product' => $this->get_table('catalog_product_entity')], 'parent_product.entity_id = i.entity_id', [])->join(['bo' => $this->get_table('catalog_product_bundle_option')], "bo.parent_id = parent_product.{$link_field} AND bo.option_id = {$bundle_selection_table}.option_id", ['option_id'])->join(['bs' => $this->get_table('catalog_product_bundle_selection')], "bs.option_id = bo.option_id AND bs.selection_id = {$bundle_selection_table}.selection_id", ['selection_id']);
        return $select;
    }
    /**
     * Apply selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function apply_fixed_bundle_selection_price()
    {
        $connection = $this->get_connection();
        $selection_price_value = 'bsp.selection_price_value';
        $selection_price_type = 'bsp.selection_price_type';
        $price_expr = new \Zend_Db_Expr($connection->get_check_sql($selection_price_type . ' = 1', 'ROUND(i.price * (' . $selection_price_value . ' / 100),4)', $connection->get_check_sql('i.special_price > 0 AND i.special_price < 100', 'ROUND(' . $selection_price_value . ' * (i.special_price / 100),4)', $selection_price_value)) . '* bs.selection_qty');
        $tier_expr = $connection->get_check_sql('i.base_tier IS NOT NULL', $connection->get_check_sql($selection_price_type . ' = 1', 'ROUND(i.base_tier - (i.base_tier * (' . $selection_price_value . ' / 100)),4)', $connection->get_check_sql('i.tier_percent > 0', 'ROUND((1 - i.tier_percent / 100) * ' . $selection_price_value . ',4)', $selection_price_value)) . ' * bs.selection_qty', 'NULL');
        $price_expr = $connection->get_least_sql([$price_expr, $connection->get_if_null_sql($tier_expr, $price_expr)]);
        $select = $this->get_base_bundle_selection_price_update_select();
        $select->join_inner(['bsp' => $this->get_table('catalog_product_bundle_selection_price')], 'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id', [])->where('i.price_type=?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED)->columns(['group_type' => $connection->get_check_sql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'), 'is_required' => 'bo.required', 'price' => $price_expr, 'tier_price' => $tier_expr]);
        $query = $select->cross_update_from_select($this->get_bundle_selection_table());
        $connection->query($query);
    }
    /**
     * Calculate selections price for fixed bundles
     *
     * @return void
     * @throws \Exception
     */
    private function calculate_fixed_bundle_selection_price()
    {
        $connection = $this->get_connection();
        $selection_price_value = 'bs.selection_price_value';
        $selection_price_type = 'bs.selection_price_type';
        $price_expr = new \Zend_Db_Expr($connection->get_check_sql($selection_price_type . ' = 1', 'ROUND(i.price * (' . $selection_price_value . ' / 100),4)', $connection->get_check_sql('i.special_price > 0 AND i.special_price < 100', 'ROUND(' . $selection_price_value . ' * (i.special_price / 100),4)', $selection_price_value)) . '* bs.selection_qty');
        $tier_expr = $connection->get_check_sql('i.base_tier IS NOT NULL', $connection->get_check_sql($selection_price_type . ' = 1', 'ROUND(i.base_tier - (i.base_tier * (' . $selection_price_value . ' / 100)),4)', $connection->get_check_sql('i.tier_percent > 0', 'ROUND((1 - i.tier_percent / 100) * ' . $selection_price_value . ',4)', $selection_price_value)) . ' * bs.selection_qty', 'NULL');
        $price_expr = $connection->get_least_sql([$price_expr, $connection->get_if_null_sql($tier_expr, $price_expr)]);
        $select = $this->get_base_bundle_selection_price_select();
        $select->where('i.price_type=?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED)->columns(['group_type' => $connection->get_check_sql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'), 'is_required' => 'bo.required', 'price' => $price_expr, 'tier_price' => $tier_expr]);
        $this->table_maintainer->insert_from_select($select, $this->get_bundle_selection_table(), []);
        $this->apply_fixed_bundle_selection_price();
    }
    /**
     * Calculate selections price for dynamic bundles
     *
     * @param array $dimensions
     * @return void
     * @throws \Exception
     */
    private function calculate_dynamic_bundle_selection_price(array $dimensions): void
    {
        $connection = $this->get_connection();
        $price = 'idx.min_price * bs.selection_qty';
        $special_expr = $connection->get_check_sql('i.special_price > 0 AND i.special_price < 100', 'ROUND(' . $price . ' * (i.special_price / 100), 4)', $price);
        $tier_expr = $connection->get_check_sql('i.tier_percent IS NOT NULL', 'ROUND((1 - i.tier_percent / 100) * ' . $price . ', 4)', 'NULL');
        $price_expr = $connection->get_least_sql([$special_expr, $connection->get_if_null_sql($tier_expr, $price)]);
        $select = $this->get_base_bundle_selection_price_select();
        $select->join(['idx' => $this->get_main_table($dimensions)], 'bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id' . ' AND i.website_id = idx.website_id', [])->where('i.price_type=?', \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC)->columns(['group_type' => $connection->get_check_sql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'), 'is_required' => 'bo.required', 'price' => $price_expr, 'tier_price' => $tier_expr]);
        $select = $this->stock_status_query_processor->execute($select);
        $query = str_replace('AS `idx`', 'AS `idx` USE INDEX (PRIMARY)', (string) $select);
        $insert_columns = ['entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'group_type', 'is_required', 'price', 'tier_price'];
        $insert_columns = array_map(function ($item) use ($connection) {
            return $connection->quote_identifier($item);
        }, $insert_columns);
        $update_values = [];
        foreach ($insert_columns as $column) {
            $update_values[] = sprintf('%s = VALUES(%s)', $column, $column);
        }
        $connection->query(sprintf('INSERT INTO `' . $this->get_bundle_selection_table() . '` (%s) %s ON DUPLICATE KEY UPDATE %s', implode(',', $insert_columns), $query, implode(',', $update_values)));
    }
    /**
     * Prepare percentage tier price for bundle products
     *
     * @param array $dimensions
     * @param array $entityIds
     * @return void
     * @throws \Exception
     */
    private function prepare_tier_price_index($dimensions, $entity_ids)
    {
        $connection = $this->get_connection();
        $metadata = $this->metadata_pool->get_metadata(Product_Interface::class);
        $link_field = $metadata->get_link_field();
        // remove index by bundle products
        $select = $connection->select()->from(['i' => $this->get_table('catalog_product_index_tier_price')], null)->join(['e' => $this->get_table('catalog_product_entity')], 'i.entity_id=e.entity_id', [])->where('e.type_id=?', \Magento\Bundle\Ui\Data_Provider\Product\Listing\Collector\Bundle_Price::PRODUCT_TYPE);
        $query = $select->delete_from_select('i');
        $connection->query($query);
        $select = $connection->select()->from(['tp' => $this->get_table('catalog_product_entity_tier_price')], ['e.entity_id'])->join(['e' => $this->get_table('catalog_product_entity')], "tp.{$link_field} = e.{$link_field}", [])->join(['cg' => $this->get_table('customer_group')], 'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)', ['customer_group_id'])->join(['pw' => $this->get_table('store_website')], 'tp.website_id = 0 OR tp.website_id = pw.website_id', ['website_id'])->join_left(
            // customer group website limitations
            ['cgw' => $this->get_table('customer_group_excluded_website')],
            'cg.customer_group_id = cgw.customer_group_id AND pw.website_id = cgw.website_id',
            []
        )->where('pw.website_id != 0')->where('e.type_id=?', \Magento\Bundle\Ui\Data_Provider\Product\Listing\Collector\Bundle_Price::PRODUCT_TYPE)->columns(new \Zend_Db_Expr('MIN(tp.value)'))->group(['e.entity_id', 'cg.customer_group_id', 'pw.website_id']);
        if (!empty($entity_ids)) {
            $select->where('e.entity_id IN(?)', $entity_ids);
        }
        // exclude websites that are limited for customer group
        $select->where('cgw.website_id IS NULL');
        foreach ($dimensions as $dimension) {
            if (!isset($this->dimension_to_field_mapper[$dimension->get_name()])) {
                throw new \LogicException('Provided dimension is not valid for Price indexer: ' . $dimension->get_name());
            }
            $select->where($this->dimension_to_field_mapper[$dimension->get_name()] . ' = ?', $dimension->get_value());
        }
        $this->table_maintainer->insert_from_select($select, $this->get_table('catalog_product_index_tier_price'), []);
    }
    /**
     * Create bundle price.
     *
     * @param IndexTableStructure $priceTable
     * @return void
     */
    private function apply_bundle_price($price_table): void
    {
        $select = $this->get_connection()->select();
        $select->from($this->get_bundle_price_table(), ['entity_id', 'customer_group_id', 'website_id', 'tax_class_id', 'orig_price', 'price', 'min_price', 'max_price', 'tier_price']);
        $this->table_maintainer->insert_from_select($select, $price_table->get_table_name(), ['entity_id', 'customer_group_id', 'website_id', 'tax_class_id', 'price', 'final_price', 'min_price', 'max_price', 'tier_price']);
    }
    /**
     * Make insert/update bundle option price.
     *
     * @return void
     * @param IndexTableStructure $priceTable
     */
    private function apply_bundle_option_price($price_table): void
    {
        $connection = $this->get_connection();
        $sub_select = $connection->select()->from($this->get_bundle_option_table(), ['entity_id', 'customer_group_id', 'website_id', 'min_price' => new \Zend_Db_Expr('SUM(min_price)'), 'alt_price' => new \Zend_Db_Expr('MIN(alt_price)'), 'max_price' => new \Zend_Db_Expr('SUM(max_price)'), 'tier_price' => new \Zend_Db_Expr('SUM(tier_price)'), 'alt_tier_price' => new \Zend_Db_Expr('MIN(alt_tier_price)')])->group(['entity_id', 'customer_group_id', 'website_id']);
        $min_price = 'i.min_price + ' . $connection->get_if_null_sql('io.min_price', '0');
        $tier_price = 'i.tier_price + ' . $connection->get_if_null_sql('io.tier_price', '0');
        $select = $connection->select()->join(['io' => $sub_select], 'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' . ' AND i.website_id = io.website_id', [])->columns(['min_price' => $connection->get_check_sql("{$min_price} = 0", 'io.alt_price', $min_price), 'max_price' => new \Zend_Db_Expr('io.max_price + i.max_price'), 'tier_price' => $connection->get_check_sql("{$tier_price} = 0", 'io.alt_tier_price', $tier_price)]);
        $query = $select->cross_update_from_select(['i' => $price_table->get_table_name()]);
        $connection->query($query);
    }
    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function get_main_table($dimensions)
    {
        if ($this->full_reindex_action) {
            return $this->table_maintainer->get_main_replica_table($dimensions);
        }
        return $this->table_maintainer->get_main_table_by_dimensions($dimensions);
    }
    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    private function get_connection(): \Magento\Framework\DB\Adapter\Adapter_Interface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->get_connection($this->connection_name);
        }
        return $this->connection;
    }
    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function get_table($table_name)
    {
        return $this->resource->get_table_name($table_name, $this->connection_name);
    }
}