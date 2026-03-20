<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Synchronized extends \Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection
{
    /**
     * Unread message list
     *
     * @var \Magento\Framework\Notification\MessageInterface[]
     */
    protected $_unread_messages = [];
    /**
     * Store new messages in database and remove outdated messages
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function _after_load(): static
    {
        $messages = $this->_message_list->as_array();
        $persisted = [];
        $unread = [];
        foreach ($messages as $message) {
            if ($message->is_displayed()) {
                foreach ($this->_items as $persisted_key => $persisted_message) {
                    if ($message->get_identity() == $persisted_message->get_identity()) {
                        $persisted[$persisted_key] = $persisted_message;
                        continue 2;
                    }
                }
                $unread[] = $message;
            }
        }
        $removed = array_diff_key($this->_items, $persisted);
        foreach ($removed as $removed_item) {
            $removed_item->delete();
        }
        foreach ($unread as $unread_item) {
            $item = $this->get_new_empty_item();
            $item->set_identity($unread_item->get_identity())->set_severity($unread_item->get_severity())->save();
        }
        if (count($removed) || count($unread)) {
            $this->_unread_messages = $unread;
            $this->clear();
            $this->load();
        } else {
            parent::_after_load();
        }
        return $this;
    }
    /**
     * Retrieve list of unread messages
     *
     * @return \Magento\Framework\Notification\MessageInterface[]
     */
    public function get_unread()
    {
        return $this->_unread_messages;
    }
}