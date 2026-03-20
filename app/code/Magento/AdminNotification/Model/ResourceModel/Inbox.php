<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model;

/**
 * Inbox resource model
 *
 * @api
 * @since 100.0.2
 */
class Inbox extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * AdminNotification Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('adminnotification_inbox', 'notification_id');
    }
    /**
     * Load latest notice
     *
     * @return $this
     */
    public function load_latest_notice(\Magento\Admin_Notification\Model\Inbox $object): static
    {
        $connection = $this->get_connection();
        $select = $connection->select()->from($this->get_main_table())->order($this->get_id_field_name() . ' DESC')->where('is_read != 1')->where('is_remove != 1')->limit(1);
        $data = $connection->fetch_row($select);
        if ($data) {
            $object->set_data($data);
        }
        $this->_after_load($object);
        return $this;
    }
    /**
     * Get notifications grouped by severity
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_notice_status(\Magento\Admin_Notification\Model\Inbox $object)
    {
        $connection = $this->get_connection();
        $select = $connection->select()->from($this->get_main_table(), ['severity' => 'severity', 'count_notice' => new \Zend_Db_Expr('COUNT(' . $this->get_id_field_name() . ')')])->group('severity')->where('is_remove=?', 0)->where('is_read=?', 0);
        return $connection->fetch_pairs($select);
    }
    /**
     * Save notifications (if not exists)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function parse(\Magento\Admin_Notification\Model\Inbox $object, array $data): void
    {
        $connection = $this->get_connection();
        foreach ($data as $item) {
            $select = $connection->select()->from($this->get_main_table())->where('title = ?', $item['title']);
            if (empty($item['url'])) {
                $select->where('url IS NULL');
            } else {
                $select->where('url = ?', $item['url']);
            }
            if (isset($item['internal'])) {
                $row = false;
                unset($item['internal']);
            } else {
                $row = $connection->fetch_row($select);
            }
            if (!$row) {
                $connection->insert($this->get_main_table(), $item);
            }
        }
    }
}