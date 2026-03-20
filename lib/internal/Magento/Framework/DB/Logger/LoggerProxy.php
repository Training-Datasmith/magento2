<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\Logger_Interface;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
class Logger_Proxy implements Logger_Interface, Reset_After_Request_Interface
{
    /**
     * Configuration group name
     */
    public const CONF_GROUP_NAME = 'db_logger';
    /**
     * Logger alias param name
     */
    public const PARAM_ALIAS = 'output';
    /**
     * Logger log all param name
     */
    public const PARAM_LOG_ALL = 'log_everything';
    /**
     * Logger query time param name
     */
    public const PARAM_QUERY_TIME = 'query_time_threshold';
    /**
     * Logger call stack param name
     */
    public const PARAM_CALL_STACK = 'include_stacktrace';
    /**
     * Logger call no index detection param name
     */
    public const PARAM_INDEX_CHECK = 'include_index_check';
    /**
     * File logger alias
     */
    public const LOGGER_ALIAS_FILE = 'file';
    /**
     * Quiet logger alias
     */
    public const LOGGER_ALIAS_DISABLED = 'disabled';
    /**
     * @var LoggerInterface|null
     */
    private ?Logger_Interface $logger = null;
    /**
     * @var FileFactory
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly File_Factory $file_factory;
    /**
     * @var QuietFactory
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Quiet_Factory $quiet_factory;
    /**
     * @var string|null
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ?string $logger_alias;
    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $log_all_queries;
    /**
     * @var float
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly float $log_query_time;
    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $log_call_stack;
    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $log_index_check;
    /**
     * LoggerProxy constructor.
     * @param FileFactory $fileFactory
     * @param QuietFactory $quietFactory
     * @param string|null $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param bool $logIndexCheck
     */
    public function __construct(File_Factory $file_factory, Quiet_Factory $quiet_factory, $logger_alias, $log_all_queries = true, $log_query_time = 0.001, $log_call_stack = true, $log_index_check = false)
    {
        $this->file_factory = $file_factory;
        $this->quiet_factory = $quiet_factory;
        $this->logger_alias = $logger_alias;
        $this->log_all_queries = $log_all_queries;
        $this->log_query_time = $log_query_time;
        $this->log_call_stack = $log_call_stack;
        $this->log_index_check = $log_index_check;
    }
    /**
     * Get logger object. Initialize if needed.
     *
     * @return LoggerInterface
     */
    private function get_logger()
    {
        if ($this->logger === null) {
            switch ($this->logger_alias) {
                case self::LOGGER_ALIAS_FILE:
                    $this->logger = $this->file_factory->create(['logAllQueries' => $this->log_all_queries, 'logQueryTime' => $this->log_query_time, 'logCallStack' => $this->log_call_stack, 'logIndexCheck' => $this->log_index_check]);
                    break;
                default:
                    $this->logger = $this->quiet_factory->create();
                    break;
            }
        }
        return $this->logger;
    }
    /**
     * Adds log record
     *
     * @param string $str
     * @return void
     */
    public function log($str)
    {
        $this->get_logger()->log($str);
    }
    /**
     * Log stats
     *
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     */
    public function log_stats($type, $sql, $bind = [], $result = null)
    {
        $this->get_logger()->log_stats($type, $sql, $bind, $result);
    }
    /**
     * Logs critical exception
     *
     * @param \Exception $exception
     * @return void
     */
    public function critical(\Exception $exception)
    {
        $this->get_logger()->critical($exception);
    }
    /**
     * Starts timer
     *
     * @return void
     */
    public function start_timer()
    {
        $this->get_logger()->start_timer();
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->logger = null;
    }
}