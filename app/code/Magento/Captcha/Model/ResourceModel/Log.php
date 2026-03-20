<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Model\Resource_Model;

use Php_Db\Sql\Expression;
/**
 * Log Attempts resource
 */
class Log extends \Magento\Framework\Model\Resource_Model\Db\Abstract_Db
{
    /**
     * Remote Address log type
     */
    public const TYPE_REMOTE_ADDRESS = 1;
    /**
     * Type User Login Name
     */
    public const TYPE_LOGIN = 2;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_core_date;
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remote_address;
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param string $connectionName
     */
    public function __construct(\Magento\Framework\Model\Resource_Model\Db\Context $context, \Magento\Framework\Stdlib\DateTime\DateTime $core_date, \Magento\Framework\HTTP\Php_Environment\Remote_Address $remote_address, $connection_name = null)
    {
        $this->_core_date = $core_date;
        $this->_remote_address = $remote_address;
        parent::__construct($context, $connection_name);
    }
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_set_main_table('captcha_log');
    }
    /**
     * Save or Update count Attempts
     *
     * @param string|null $login
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function log_attempt($login)
    {
        if ($login != null) {
            $this->get_connection()->insert_on_duplicate($this->get_main_table(), ['type' => self::TYPE_LOGIN, 'value' => $login, 'count' => 1, 'updated_at' => $this->_core_date->gmt_date()], ['count' => new Expression('count+1'), 'updated_at']);
        }
        $ip = $this->_remote_address->get_remote_address();
        if ($ip != null) {
            $this->get_connection()->insert_on_duplicate($this->get_main_table(), ['type' => self::TYPE_REMOTE_ADDRESS, 'value' => $ip, 'count' => 1, 'updated_at' => $this->_core_date->gmt_date()], ['count' => new Expression('count+1'), 'updated_at']);
        }
        return $this;
    }
    /**
     * Delete User attempts by login
     *
     * @param string $login
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete_user_attempts($login)
    {
        if ($login != null) {
            $this->get_connection()->delete($this->get_main_table(), ['type = ?' => self::TYPE_LOGIN, 'value = ?' => $login]);
        }
        $ip = $this->_remote_address->get_remote_address();
        if ($ip != null) {
            $this->get_connection()->delete($this->get_main_table(), ['type = ?' => self::TYPE_REMOTE_ADDRESS, 'value = ?' => $ip]);
        }
        return $this;
    }
    /**
     * Get count attempts by ip
     *
     * @return null|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function count_attempts_by_remote_address()
    {
        $ip = $this->_remote_address->get_remote_address();
        if (!$ip) {
            return 0;
        }
        $connection = $this->get_connection();
        $select = $connection->select()->from($this->get_main_table(), 'count')->where('type = ?', self::TYPE_REMOTE_ADDRESS)->where('value = ?', $ip);
        return $connection->fetch_one($select);
    }
    /**
     * Get count attempts by user login
     *
     * @param string $login
     * @return null|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function count_attempts_by_user_login($login)
    {
        if (!$login) {
            return 0;
        }
        $connection = $this->get_connection();
        $select = $connection->select()->from($this->get_main_table(), 'count')->where('type = ?', self::TYPE_LOGIN)->where('value = ?', $login);
        return $connection->fetch_one($select);
    }
    /**
     * Delete attempts with expired in update_at time
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete_old_attempts()
    {
        $this->get_connection()->delete($this->get_main_table(), ['updated_at < ?' => $this->_core_date->gmt_date(null, time() - 60 * 30)]);
    }
}