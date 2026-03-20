<?php

declare (strict_types=1);
/**
 * Magento profiler for requests to database
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

class Profiler extends \Zend_Db_Profiler
{
    /**
     * Host IP whereto a request is sent
     *
     * @var string
     */
    protected $_host = '';
    /**
     * Database connection type
     *
     * @var string
     */
    protected $_type = '';
    /**
     * Last query Id
     *
     * @var string|null
     */
    private $_last_query_id = null;
    /**
     * Setter for host IP
     *
     * @param string $host
     * @return \Magento\Framework\DB\Profiler
     */
    public function set_host($host)
    {
        $this->_host = $host;
        return $this;
    }
    /**
     * Setter for database connection type
     *
     * @param string $type
     * @return \Magento\Framework\DB\Profiler
     */
    public function set_type($type)
    {
        $this->_type = $type;
        return $this;
    }
    /**
     * Starts a query. Creates a new query profile object (\Zend_Db_Profiler_Query)
     *
     * @param string $queryText SQL statement
     * @param integer|null $queryType OPTIONAL Type of query, one of the \Zend_Db_Profiler::* constants
     * @return integer|null
     */
    public function query_start($query_text, $query_type = null)
    {
        $this->_last_query_id = parent::query_start($query_text, $query_type);
        return $this->_last_query_id;
    }
    /**
     * Ends a query. Pass it the handle that was returned by queryStart().
     *
     * @param int $queryId
     * @return string|void
     */
    public function query_end($query_id)
    {
        $this->_last_query_id = null;
        return parent::query_end($query_id);
    }
    /**
     * Ends the last query if exists. Used for finalize broken queries.
     *
     * @return string|void
     */
    public function query_end_last()
    {
        if ($this->_last_query_id !== null) {
            return $this->query_end($this->_last_query_id);
        }
        return self::IGNORED;
    }
}