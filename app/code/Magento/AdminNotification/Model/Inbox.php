<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model;

use Magento\Framework\Notification\Message_Interface;
use Magento\Framework\Notification\Notifier_Interface;
/**
 * AdminNotification Inbox model
 *
 * @method int getSeverity()
 * @method \Magento\AdminNotification\Model\Inbox setSeverity(int $value)
 * @method string getDateAdded()
 * @method \Magento\AdminNotification\Model\Inbox setDateAdded(string $value)
 * @method string getTitle()
 * @method \Magento\AdminNotification\Model\Inbox setTitle(string $value)
 * @method string getDescription()
 * @method \Magento\AdminNotification\Model\Inbox setDescription(string $value)
 * @method string getUrl()
 * @method \Magento\AdminNotification\Model\Inbox setUrl(string $value)
 * @method int getIsRead()
 * @method \Magento\AdminNotification\Model\Inbox setIsRead(int $value)
 * @method int getIsRemove()
 * @method \Magento\AdminNotification\Model\Inbox setIsRemove(int $value)
 *
 * @api
 * @since 100.0.2
 */
class Inbox extends \Magento\Framework\Model\Abstract_Model implements Notifier_Interface, Inbox_Interface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Admin_Notification\Model\Resource_Model\Inbox::class);
    }
    /**
     * @inheritdoc
     */
    public function get_severities($severity = null): \Magento\Framework\Phrase|null|array
    {
        $severities = [Message_Interface::SEVERITY_CRITICAL => __('critical'), Message_Interface::SEVERITY_MAJOR => __('major'), Message_Interface::SEVERITY_MINOR => __('minor'), Message_Interface::SEVERITY_NOTICE => __('notice')];
        if ($severity !== null) {
            return $severities[$severity] ?? null;
        }
        return $severities;
    }
    /**
     * @inheritdoc
     */
    public function load_latest_notice(): static
    {
        $this->set_data([]);
        $this->get_resource()->load_latest_notice($this);
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function get_notice_status()
    {
        return $this->get_resource()->get_notice_status($this);
    }
    /**
     * Parse and save new data
     *
     * @return $this
     */
    public function parse(array $data): static
    {
        $this->get_resource()->parse($this, $data);
        return $this;
    }
    /**
     * Add new message
     *
     * @param int $severity
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function add($severity, $title, $description, $url = '', $is_internal = true): static
    {
        if (!$this->get_severities($severity)) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Wrong message type'));
        }
        if (is_array($description)) {
            $description = '<ul><li>' . implode('</li><li>', $description) . '</li></ul>';
        }
        $date = date('Y-m-d H:i:s');
        $this->parse([['severity' => $severity, 'date_added' => $date, 'title' => $title, 'description' => $description, 'url' => $url, 'internal' => $is_internal]]);
        return $this;
    }
    /**
     * Add critical severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function add_critical($title, $description, $url = '', $is_internal = true): static
    {
        $this->add(Message_Interface::SEVERITY_CRITICAL, $title, $description, $url, $is_internal);
        return $this;
    }
    /**
     * Add major severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function add_major($title, $description, $url = '', $is_internal = true): static
    {
        $this->add(Message_Interface::SEVERITY_MAJOR, $title, $description, $url, $is_internal);
        return $this;
    }
    /**
     * Add minor severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function add_minor($title, $description, $url = '', $is_internal = true): static
    {
        $this->add(Message_Interface::SEVERITY_MINOR, $title, $description, $url, $is_internal);
        return $this;
    }
    /**
     * Add notice
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function add_notice($title, $description, $url = '', $is_internal = true): static
    {
        $this->add(Message_Interface::SEVERITY_NOTICE, $title, $description, $url, $is_internal);
        return $this;
    }
}