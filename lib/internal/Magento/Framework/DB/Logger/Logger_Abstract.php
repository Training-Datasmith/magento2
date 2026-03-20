<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\DB\Logger_Interface;
use Magento\Framework\Debug;
use Zend_Db_Statement_Pdo;
abstract class Logger_Abstract implements Logger_Interface
{
    private const LINE_DELIMITER = "\n";
    /**
     * @var int
     */
    private $timer;
    /**
     * @var bool
     */
    private $log_all_queries;
    /**
     * @var float
     */
    private $log_query_time;
    /**
     * @var bool
     */
    private $log_call_stack;
    /**
     * @var bool
     */
    private bool $log_index_check;
    /**
     * @var QueryAnalyzerInterface
     */
    private Query_Analyzer_Interface $query_analyzer;
    /**
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param bool $logIndexCheck
     * @param QueryAnalyzerInterface|null $queryAnalyzer
     */
    public function __construct($log_all_queries = false, $log_query_time = 0.05, $log_call_stack = false, $log_index_check = false, ?Query_Analyzer_Interface $query_analyzer = null)
    {
        $this->log_all_queries = $log_all_queries;
        $this->log_query_time = $log_query_time;
        $this->log_call_stack = $log_call_stack;
        $this->log_index_check = $log_index_check;
        $this->query_analyzer = $query_analyzer ?: Object_Manager::get_instance()->get(Query_Analyzer_Interface::class);
    }
    /**
     * @inheritDoc
     */
    public function start_timer()
    {
        $this->timer = microtime(true);
    }
    /**
     * Get formatted statistics message
     *
     * @param string $type Type of query
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    public function get_stats($type, $sql, $bind = [], $result = null)
    {
        $time = sprintf('%.4f', microtime(true) - $this->timer);
        if (!$this->log_all_queries && $time < $this->log_query_time) {
            return '';
        }
        if ($this->is_explain_query($sql)) {
            return '';
        }
        return $this->build_debug_message($type, $sql, $bind, $result, $time);
    }
    /**
     * Check if query already contains 'explain' keyword
     *
     * @param string $query
     * @return bool
     */
    private function is_explain_query(string $query): bool
    {
        // Remove leading/trailing whitespace and normalize case
        $cleaned = ltrim($query);
        // Strip comments
        while (preg_match('/^(--[^\n]*\n|\/\*.*?\*\/\s*)/s', $cleaned, $matches)) {
            $cleaned = ltrim(substr($cleaned, strlen($matches[0])));
        }
        // Check if it starts with EXPLAIN
        return (bool) preg_match('/^EXPLAIN\b/i', $cleaned);
    }
    /**
     * Build log message based on query type
     *
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param Zend_Db_Statement_Pdo|null $result
     * @param string $time
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    private function build_debug_message(string $type, string $sql, array $bind, ?Zend_Db_Statement_Pdo $result, string $time): string
    {
        $message = '## ' . getmypid() . ' ## ';
        switch ($type) {
            case self::TYPE_CONNECT:
                $message .= 'CONNECT' . self::LINE_DELIMITER;
                break;
            case self::TYPE_TRANSACTION:
                $message .= 'TRANSACTION ' . $sql . self::LINE_DELIMITER;
                break;
            case self::TYPE_QUERY:
                $message .= 'QUERY' . self::LINE_DELIMITER;
                $message .= 'SQL: ' . $sql . self::LINE_DELIMITER;
                if ($bind) {
                    $message .= 'BIND: ' . var_export($bind, true) . self::LINE_DELIMITER;
                }
                if ($result instanceof \Zend_Db_Statement_Pdo) {
                    $message .= 'AFF: ' . $result->row_count() . self::LINE_DELIMITER;
                }
                if ($this->log_index_check) {
                    try {
                        $message .= $this->process_index_check($sql, $bind) . self::LINE_DELIMITER;
                    } catch (Query_Analyzer_Exception $e) {
                        $message .= 'INDEX CHECK: ' . strtoupper($e->get_message()) . self::LINE_DELIMITER;
                    }
                }
                break;
        }
        $message .= 'TIME: ' . $time . self::LINE_DELIMITER;
        if ($this->log_call_stack) {
            $message .= $this->get_call_stack();
        }
        $message .= self::LINE_DELIMITER;
        return $message;
    }
    /**
     * Get potential index issues
     *
     * @param string $sql
     * @param array $bind
     * @return string
     * @throws QueryAnalyzerException
     */
    private function process_index_check(string $sql, array $bind): string
    {
        $message = '';
        $issues = $this->query_analyzer->process($sql, $bind);
        if (!empty($issues)) {
            $message .= 'INDEX CHECK: POTENTIAL ISSUES - ' . implode(', ', array_unique($issues));
        } else {
            $message .= 'INDEX CHECK: USING INDEX';
        }
        return $message;
    }
    /**
     * Get call stack debug message
     *
     * @return string
     */
    private function get_call_stack(): string
    {
        return 'TRACE: ' . Debug::backtrace(true, false) . self::LINE_DELIMITER;
    }
}