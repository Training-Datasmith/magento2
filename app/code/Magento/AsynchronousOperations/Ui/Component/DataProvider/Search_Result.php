<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Data_Provider;

use Magento\Asynchronous_Operations\Model\Bulk_Status\Calculated_Status_Sql;
use Magento\Asynchronous_Operations\Model\Status_Mapper;
use Magento\Authorization\Model\User_Context_Interface;
use Magento\Framework\Bulk\Bulk_Summary_Interface;
use Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface as FetchStrategy;
use Magento\Framework\Data\Collection\Entity_Factory_Interface as EntityFactory;
use Magento\Framework\Event\Manager_Interface as EventManager;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Psr\Log\Logger_Interface as Logger;
class Search_Result extends \Magento\Framework\View\Element\Ui_Component\Data_Provider\Search_Result
{
    /**
     * @var array|int
     */
    private $operation_status;
    /**
     * @param string $mainTable
     * @param AbstractResource $resourceModel
     * @param string $identifierName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Entity_Factory $entity_factory, Logger $logger, Fetch_Strategy $fetch_strategy, Event_Manager $event_manager, private readonly User_Context_Interface $user_context, private readonly Status_Mapper $status_mapper, private readonly Calculated_Status_Sql $calculated_status_sql, $main_table = 'magento_bulk', $resource_model = null, $identifier_name = 'uuid')
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $main_table, $resource_model, $identifier_name);
    }
    /**
     * @inheritdoc
     */
    protected function _init_select(): static
    {
        $this->get_select()->from(['main_table' => $this->get_main_table()], ['*', 'status' => $this->calculated_status_sql->get($this->get_table('magento_operation'))])->where('user_id=?', $this->user_context->get_user_id())->where('user_type=?', User_Context_Interface::USER_TYPE_ADMIN)->or_where('user_type=?', User_Context_Interface::USER_TYPE_INTEGRATION);
        return $this;
    }
    /**
     * @inheritdoc
     */
    protected function _after_load()
    {
        /** @var BulkSummaryInterface $item */
        foreach ($this->get_items() as $item) {
            $item->set_status($this->status_mapper->operation_status_to_bulk_summary_status($item->get_status()));
        }
        return parent::_after_load();
    }
    /**
     * @inheritdoc
     */
    public function add_field_to_filter($field, $condition = null)
    {
        if ($field == 'status') {
            if (is_array($condition)) {
                foreach ($condition as $value) {
                    $this->operation_status = $this->status_mapper->bulk_summary_status_to_operation_status($value);
                    if (is_array($this->operation_status)) {
                        foreach ($this->operation_status as $status_value) {
                            $this->get_select()->or_having('status = ?', $status_value);
                        }
                        continue;
                    }
                    $this->get_select()->having('status = ?', $this->operation_status);
                }
            }
            return $this;
        }
        return parent::add_field_to_filter($field, $condition);
    }
    /**
     * @inheritdoc
     */
    public function get_select_count_sql()
    {
        $select = parent::get_select_count_sql();
        $select->columns(['status' => $this->calculated_status_sql->get($this->get_table('magento_operation'))]);
        //add grouping by status if filtering by status was executed
        if (isset($this->operation_status)) {
            $select->group('status');
        }
        return $select;
    }
}