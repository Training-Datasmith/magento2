<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model\System\Message;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\Resource_Model\Db\Collection\Abstract_Collection
{
    /**
     * Number of messages by severity
     *
     * @var array
     */
    protected $_count_by_severity = [];
    /**
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\Entity_Factory $entity_factory,
        \Psr\Log\Logger_Interface $logger,
        \Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface $fetch_strategy,
        \Magento\Framework\Event\Manager_Interface $event_manager,
        /**
         * System message list
         */
        protected \Magento\Framework\Notification\Message_List $_message_list,
        ?\Magento\Framework\DB\Adapter\Adapter_Interface $connection = null,
        ?\Magento\Framework\Model\Resource_Model\Db\Abstract_Db $resource = null
    )
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $connection, $resource);
    }
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Admin_Notification\Model\System\Message::class, \Magento\Admin_Notification\Model\Resource_Model\System\Message::class);
    }
    /**
     * Initialize db query
     *
     * @return void
     */
    protected function _init_select()
    {
        parent::_init_select();
        $this->add_order('severity', self::SORT_ORDER_ASC)->add_order('created_at');
    }
    /**
     * Initialize system messages after load
     *
     * @return void
     */
    protected function _after_load()
    {
        foreach ($this->_items as $key => $item) {
            $message = $this->_message_list->get_message_by_identity($item->get_identity());
            if ($message) {
                $item->set_text($message->get_text());
                if (array_key_exists($message->get_severity(), $this->_count_by_severity)) {
                    $this->_count_by_severity[$message->get_severity()]++;
                } else {
                    $this->_count_by_severity[$message->get_severity()] = 1;
                }
            } else {
                unset($this->_items[$key]);
            }
        }
    }
    /**
     * Set message severity filter
     *
     * @param int $severity
     * @return $this
     */
    public function set_severity($severity): static
    {
        $this->add_field_to_filter('severity', ['eq' => $severity * 1]);
        return $this;
    }
    /**
     * Retrieve number of messages by severity
     *
     * @param int $severity
     * @return int
     */
    public function get_count_by_severity($severity)
    {
        return $this->_count_by_severity[$severity] ?? 0;
    }
}