<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Resource_Model\Selection;

use Magento\Catalog\Model\Resource_Model\Product\Collection\Product_Limitation_Factory;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data_Object;
use Magento\Framework\DB\Select;
/**
 * Bundle Selections Resource Collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\Resource_Model\Product\Collection
{
    /**
     * Selection table name
     *
     * @var string
     */
    protected $_selection_table;
    /**
     * @var DataObject
     */
    private $item_prototype = null;
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor
     */
    private $catalog_rule_processor = null;
    /**
     * Is website scope prices joined to collection
     *
     * @var bool
     */
    private $website_scope_price_joined = false;
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
     */
    private $stock_item;
    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param \Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer|null $tableMaintainer
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Item|null $stockItem
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\Data\Collection\Entity_Factory $entity_factory, \Psr\Log\Logger_Interface $logger, \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface $fetch_strategy, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Eav\Model\Config $eav_config, \Magento\Framework\App\Resource_Connection $resource, \Magento\Eav\Model\Entity_Factory $eav_entity_factory, \Magento\Catalog\Model\Resource_Model\Helper $resource_helper, \Magento\Framework\Validator\Universal_Factory $universal_factory, \Magento\Store\Model\Store_Manager_Interface $store_manager, \Magento\Framework\Module\Manager $module_manager, \Magento\Catalog\Model\Indexer\Product\Flat\State $catalog_product_flat_state, \Magento\Framework\App\Config\Scope_Config_Interface $scope_config, \Magento\Catalog\Model\Product\Option_Factory $product_option_factory, \Magento\Catalog\Model\Resource_Model\Url $catalog_url, \Magento\Framework\Stdlib\DateTime\Timezone_Interface $locale_date, \Magento\Customer\Model\Session $customer_session, \Magento\Framework\Stdlib\DateTime $date_time, \Magento\Customer\Api\Group_Management_Interface $group_management, ?\Magento\Framework\DB\Adapter\Adapter_Interface $connection = null, ?Product_Limitation_Factory $product_limitation_factory = null, ?\Magento\Framework\Entity_Manager\Metadata_Pool $metadata_pool = null, ?\Magento\Catalog\Model\Indexer\Category\Product\Table_Maintainer $table_maintainer = null, ?\Magento\Catalog_Inventory\Model\Resource_Model\Stock\Item $stock_item = null)
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $eav_config, $resource, $eav_entity_factory, $resource_helper, $universal_factory, $store_manager, $module_manager, $catalog_product_flat_state, $scope_config, $product_option_factory, $catalog_url, $locale_date, $customer_session, $date_time, $group_management, $connection, $product_limitation_factory, $metadata_pool, $table_maintainer);
        $this->stock_item = $stock_item ?? Object_Manager::get_instance()->get(\Magento\Catalog_Inventory\Model\Resource_Model\Stock\Item::class);
    }
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_row_id_field_name('selection_id');
        $this->_selection_table = $this->get_table('catalog_product_bundle_selection');
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        parent::_reset_state();
        $this->item_prototype = null;
        $this->catalog_rule_processor = null;
        $this->website_scope_price_joined = false;
    }
    /**
     * Set store id for each collection item when collection was loaded.
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @return $this
     */
    public function _after_load()
    {
        return parent::_after_load();
    }
    /**
     * Initialize collection select
     *
     * @return $this|void
     */
    protected function _init_select()
    {
        parent::_init_select();
        $this->get_select()->join(['selection' => $this->_selection_table], 'selection.product_id = e.entity_id', ['*']);
    }
    /**
     * Join website scope prices to collection, override default prices
     *
     * @param int $websiteId
     * @return $this
     */
    public function join_prices($website_id)
    {
        $connection = $this->get_connection();
        $price_type = $connection->get_check_sql('price.selection_price_type IS NOT NULL', 'price.selection_price_type', 'selection.selection_price_type');
        $price_value = $connection->get_check_sql('price.selection_price_value IS NOT NULL', 'price.selection_price_value', 'selection.selection_price_value');
        $this->get_select()->join_left(['price' => $this->get_table('catalog_product_bundle_selection_price')], 'selection.selection_id = price.selection_id AND price.website_id = ' . (int) $website_id . ' AND selection.parent_product_id = price.parent_product_id', ['selection_price_type' => $price_type, 'selection_price_value' => $price_value, 'parent_product_id' => 'price.parent_product_id', 'price_scope' => 'price.website_id']);
        $this->website_scope_price_joined = true;
        return $this;
    }
    /**
     * Apply option ids filter to collection
     *
     * @param array $optionIds
     * @return $this
     */
    public function set_option_ids_filter($option_ids)
    {
        if (!empty($option_ids)) {
            $this->get_select()->where('selection.option_id IN (?)', $option_ids, \Zend_Db::INT_TYPE);
        }
        return $this;
    }
    /**
     * Apply selection ids filter to collection
     *
     * @param array $selectionIds
     * @return $this
     */
    public function set_selection_ids_filter($selection_ids)
    {
        if (!empty($selection_ids)) {
            $this->get_select()->where('selection.selection_id IN (?)', $selection_ids, \Zend_Db::INT_TYPE);
        }
        return $this;
    }
    /**
     * Set position order
     *
     * @return $this
     */
    public function set_position_order()
    {
        $this->get_select()->order('selection.position asc')->order('selection.selection_id asc');
        return $this;
    }
    /**
     * Add filtering of products that have 0 items left.
     *
     * @return $this
     * @since 100.2.0
     */
    public function add_quantity_filter()
    {
        $manage_stock_expr = $this->stock_item->get_manage_stock_expr('stock_item');
        $backorders_expr = $this->stock_item->get_backorders_expr('stock_item');
        $min_qty_expr = $this->get_connection()->get_check_sql('selection.selection_can_change_qty', $this->stock_item->get_min_sale_qty_expr('stock_item'), 'selection.selection_qty');
        $where = $manage_stock_expr . ' = 0';
        $where .= ' OR (' . 'stock_item.is_in_stock = ' . \Magento\Catalog_Inventory\Model\Stock::STOCK_IN_STOCK . ' AND (' . $backorders_expr . ' != ' . \Magento\Catalog_Inventory\Model\Stock::BACKORDERS_NO . ' OR ' . $min_qty_expr . ' <= stock_item.qty' . ')' . ')';
        $this->get_select()->join_inner(['stock_item' => $this->stock_item->get_main_table()], 'selection.product_id = stock_item.product_id', [])->where($where);
        return $this;
    }
    /**
     * @inheritDoc
     * @since 100.2.0
     */
    public function get_new_empty_item()
    {
        if (null === $this->item_prototype) {
            $this->item_prototype = parent::get_new_empty_item();
        }
        return clone $this->item_prototype;
    }
    /**
     * Add filter by price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $searchMin
     * @param bool $useRegularPrice
     *
     * @return $this
     * @since 100.2.0
     */
    public function add_price_filter($product, $search_min, $use_regular_price = false)
    {
        if ($product->get_price_type() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            if (!$this->get_store_id()) {
                $this->set_store_id($this->_store_manager->get_store()->get_id());
            }
            $this->add_price_data();
            if ($use_regular_price) {
                $minimal_price_expression = self::INDEX_TABLE_ALIAS . '.price';
            } else {
                $this->get_catalog_rule_processor()->add_price_data($this, 'selection.product_id');
                $minimal_price_expression = 'LEAST(minimal_price, IFNULL(catalog_rule_price, minimal_price))';
            }
            $order_by_value = new \Zend_Db_Expr('(' . $minimal_price_expression . ' * selection.selection_qty' . ')');
        } else {
            $connection = $this->get_connection();
            $price_type = $connection->get_if_null_sql('price.selection_price_type', 'selection.selection_price_type');
            $price_value = $connection->get_if_null_sql('price.selection_price_value', 'selection.selection_price_value');
            if (!$this->website_scope_price_joined) {
                $website_id = $this->_store_manager->get_store()->get_website_id();
                $this->get_select()->join_left(['price' => $this->get_table('catalog_product_bundle_selection_price')], 'selection.selection_id = price.selection_id AND price.website_id = ' . (int) $website_id, []);
            }
            $price = $connection->get_check_sql($price_type . ' = 1', (float) $product->get_price() . ' * ' . $price_value . ' / 100', $price_value);
            $order_by_value = new \Zend_Db_Expr('(' . $price . ' * ' . 'selection.selection_qty)');
        }
        $this->get_select()->reset(Select::ORDER);
        $this->get_select()->order(new \Zend_Db_Expr($order_by_value . ($search_min ? Select::SQL_ASC : Select::SQL_DESC)));
        $this->get_select()->limit(1);
        return $this;
    }
    /**
     * Get Catalog Rule Processor.
     *
     * @return \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor
     */
    private function get_catalog_rule_processor()
    {
        if (null === $this->catalog_rule_processor) {
            $this->catalog_rule_processor = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Catalog_Rule\Model\Resource_Model\Product\Collection_Processor::class);
        }
        return $this->catalog_rule_processor;
    }
}