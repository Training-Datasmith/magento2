<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Resource_Model\Indexer;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Resource_Model\Indexer\Active_Table_Switcher;
use Magento\Catalog_Inventory\Model\Indexer\Stock\Action\Full;
use Magento\Catalog_Inventory\Model\Resource_Model\Indexer\Stock\Default_Stock;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Indexer\Table\Strategy_Interface;
use Magento\Framework\Model\Resource_Model\Db\Context;
/**
 * Bundle Stock Status Indexer Resource Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stock extends Default_Stock
{
    /**
     * @var ActiveTableSwitcher
     */
    private $active_table_switcher;
    /**
     * @var StockStatusSelectBuilder
     */
    private $stock_status_select_builder;
    /**
     * @var BundleOptionStockDataSelectBuilder
     */
    private $bundle_option_stock_data_select_builder;
    /**
     * @param Context $context
     * @param StrategyInterface $tableStrategy
     * @param Config $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param StockStatusSelectBuilder $stockStatusSelectBuilder
     * @param BundleOptionStockDataSelectBuilder $bundleOptionStockDataSelectBuilder
     * @param OptionQtyExpressionProvider $optionQtyExpressionProvider
     * @param string $connectionName
     */
    public function __construct(Context $context, Strategy_Interface $table_strategy, Config $eav_config, Scope_Config_Interface $scope_config, Active_Table_Switcher $active_table_switcher, Stock_Status_Select_Builder $stock_status_select_builder, Bundle_Option_Stock_Data_Select_Builder $bundle_option_stock_data_select_builder, private readonly Option_Qty_Expression_Provider $option_qty_expression_provider, $connection_name = null)
    {
        parent::__construct($context, $table_strategy, $eav_config, $scope_config, $connection_name);
        $this->_type_id = Type::TYPE_CODE;
        $this->_is_composite = true;
        $this->active_table_switcher = $active_table_switcher;
        $this->stock_status_select_builder = $stock_status_select_builder;
        $this->bundle_option_stock_data_select_builder = $bundle_option_stock_data_select_builder;
    }
    /**
     * Retrieve table name for temporary bundle option stock index
     *
     * @return string
     */
    protected function _get_bundle_option_table()
    {
        return $this->get_table('catalog_product_bundle_stock_index');
    }
    /**
     * Prepare stock status per Bundle options, website and stock
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     *
     * @return $this
     */
    protected function _prepare_bundle_option_stock_data($entity_ids = null, $use_primary_table = false)
    {
        $this->_clean_bundle_option_stock_data();
        $connection = $this->get_connection();
        $table = $this->get_action_type() === Full::ACTION_TYPE ? $this->active_table_switcher->get_additional_table_name($this->get_main_table()) : $this->get_main_table();
        $idx_table = $use_primary_table ? $table : $this->get_idx_table();
        $select = $this->bundle_option_stock_data_select_builder->build_select($idx_table);
        $status = $this->get_options_status_expression();
        $select->columns(['status' => $status]);
        if ($entity_ids !== null) {
            $select->where('product.entity_id IN(?)', $entity_ids);
        }
        // clone select for bundle product without required bundle options
        $select_non_required = clone $select;
        $select->where('bo.required = ?', 1);
        $select_non_required->where('bo.required = ?', 0)->having($status . ' = 1');
        $query = $select->insert_from_select($this->_get_bundle_option_table());
        $connection->query($query);
        $query = $select_non_required->insert_from_select($this->_get_bundle_option_table());
        $connection->query($query);
        return $this;
    }
    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _get_stock_status_select($entity_ids = null, $use_primary_table = false)
    {
        $this->_prepare_bundle_option_stock_data($entity_ids, $use_primary_table);
        $connection = $this->get_connection();
        $select = parent::_get_stock_status_select($entity_ids, $use_primary_table);
        $select = $this->stock_status_select_builder->build_select($select);
        $status_not_null_expr = $connection->get_check_sql('o.stock_status IS NOT NULL', 'o.stock_status', '0');
        $status_expr = $this->get_status_expression($connection);
        $select->columns(['status' => $connection->get_least_sql([new \Zend_Db_Expr('MIN(' . $status_not_null_expr . ')'), new \Zend_Db_Expr('MIN(' . $status_expr . ')')])]);
        if ($entity_ids !== null) {
            $select->where('e.entity_id IN(?)', $entity_ids);
        }
        return $select;
    }
    /**
     * Prepare stock status data in temporary index table
     *
     * @param int|array $entityIds  the product limitation
     * @return $this
     */
    protected function _prepare_index_table($entity_ids = null)
    {
        parent::_prepare_index_table($entity_ids);
        $this->_clean_bundle_option_stock_data();
        return $this;
    }
    /**
     * Update Stock status index by product ids
     *
     * @param array|int $entityIds
     *
     * @return $this
     */
    protected function _update_index($entity_ids)
    {
        parent::_update_index($entity_ids);
        $this->_clean_bundle_option_stock_data();
        return $this;
    }
    /**
     * Clean temporary bundle options stock data
     *
     * @return $this
     */
    protected function _clean_bundle_option_stock_data()
    {
        $this->get_connection()->delete($this->_get_bundle_option_table());
        return $this;
    }
    /**
     * Build expression for bundle options stock status
     *
     * @return \Zend_Db_Expr
     */
    private function get_options_status_expression(): \Zend_Db_Expr
    {
        $connection = $this->get_connection();
        $qty_expr = $this->option_qty_expression_provider->get_expression();
        $is_available_expr = $connection->get_check_sql('bs.selection_can_change_qty = 0 AND bs.selection_qty > ' . $qty_expr, '0', 'i.stock_status');
        if ($this->stock_configuration->get_backorders()) {
            $backorders_expr = $connection->get_check_sql('cisi.use_config_backorders = 0 AND cisi.backorders = 0', $is_available_expr, 'i.stock_status');
        } else {
            $backorders_expr = $connection->get_check_sql('cisi.use_config_backorders = 0 AND cisi.backorders > 0', 'i.stock_status', $is_available_expr);
        }
        if ($this->stock_configuration->get_manage_stock()) {
            $status_expr = $connection->get_check_sql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0', 1, $backorders_expr);
        } else {
            $status_expr = $connection->get_check_sql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1', $backorders_expr, 1);
        }
        return new \Zend_Db_Expr('MAX(' . $connection->get_check_sql('e.required_options = 0', $status_expr, '0') . ')');
    }
}