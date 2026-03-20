<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * @api
 * @since 100.0.2
 */
class Trigger
{
    /**#@+
     * Trigger times
     */
    public const TIME_BEFORE = 'BEFORE';
    public const TIME_AFTER = 'AFTER';
    /**#@-*/
    /**#@+
     * Trigger events
     */
    public const EVENT_INSERT = 'INSERT';
    public const EVENT_UPDATE = 'UPDATE';
    public const EVENT_DELETE = 'DELETE';
    /**#@-*/
    /**#@-*/
    protected static $list_of_times = [self::TIME_BEFORE, self::TIME_AFTER];
    /**
     * List of events available for trigger
     *
     * @var array
     */
    protected static $list_of_events = [self::EVENT_INSERT, self::EVENT_UPDATE, self::EVENT_DELETE];
    /**
     * Name of trigger
     *
     * @var string
     */
    protected $name;
    /**
     * Time of trigger
     *
     * @var string
     */
    protected $time;
    /**
     * Time of trigger
     *
     * @var string
     */
    protected $event;
    /**
     * Table name
     *
     * @var string
     */
    protected $table_name;
    /**
     * List of statements for trigger body
     *
     * @var array
     */
    protected $statements = [];
    /**
     * Set trigger name
     *
     * @param string $name
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function set_name($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger name should be a string'));
        }
        $this->name = strtolower($name);
        return $this;
    }
    /**
     * Retrieve name of trigger
     *
     * @throws \Zend_Db_Exception
     * @return string
     */
    public function get_name()
    {
        if (empty($this->name)) {
            throw new \Zend_Db_Exception((string) new \Magento\Framework\Phrase('Trigger name is not defined'));
        }
        return $this->name;
    }
    /**
     * Set trigger time
     *
     * @param string $time
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function set_time($time)
    {
        if (in_array($time, self::$list_of_times)) {
            $this->time = strtoupper($time);
        } else {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger unsupported time type'));
        }
        return $this;
    }
    /**
     * Retrieve time of trigger
     *
     * @throws \Zend_Db_Exception
     * @return string
     */
    public function get_time()
    {
        if ($this->time === null) {
            throw new \Zend_Db_Exception((string) new \Magento\Framework\Phrase('Trigger time is not defined'));
        }
        return $this->time;
    }
    /**
     * Set trigger event
     *
     * @param string $event
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function set_event($event)
    {
        if (in_array($event, self::$list_of_events)) {
            $this->event = strtoupper($event);
        } else {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger unsupported event type'));
        }
        return $this;
    }
    /**
     * Retrieve event of trigger
     *
     * @throws \Zend_Db_Exception
     * @return string
     */
    public function get_event()
    {
        if ($this->event === null) {
            throw new \Zend_Db_Exception((string) new \Magento\Framework\Phrase('Trigger event is not defined'));
        }
        return $this->event;
    }
    /**
     * Set table name
     *
     * @param string $name
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function set_table($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger table name should be a string'));
        }
        $this->table_name = $name;
        return $this;
    }
    /**
     * Retrieve table name
     *
     * @throws \Zend_Db_Exception
     * @return string
     */
    public function get_table()
    {
        if (empty($this->table_name)) {
            throw new \Zend_Db_Exception((string) new \Magento\Framework\Phrase('Trigger table name is not defined'));
        }
        return $this->table_name;
    }
    /**
     * Add statement to trigger
     *
     * @param string $statement
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Ddl\Trigger
     */
    public function add_statement($statement)
    {
        if (!is_string($statement)) {
            throw new \InvalidArgumentException((string) new \Magento\Framework\Phrase('Trigger statement should be a string'));
        }
        $statement = trim($statement);
        $statement = rtrim($statement, ';') . ';';
        $this->statements[] = $statement;
        return $this;
    }
    /**
     * Retrieve list of statements of trigger
     *
     * @return array
     */
    public function get_statements()
    {
        return $this->statements;
    }
    /**
     * Retrieve list of times available for trigger
     *
     * @return array
     */
    public static function get_list_of_times()
    {
        return self::$list_of_times;
    }
    /**
     * Retrieve list of events available for trigger
     *
     * @return array
     */
    public static function get_list_of_events()
    {
        return self::$list_of_events;
    }
}