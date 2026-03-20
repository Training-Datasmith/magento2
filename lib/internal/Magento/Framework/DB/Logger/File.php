<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write_Interface;
/**
 * Logging to file
 */
class File extends Logger_Abstract
{
    /**
     * @var WriteInterface
     */
    private $dir;
    /**
     * Path to SQL debug data log
     *
     * @var string
     */
    protected $debug_file;
    /**
     * @param Filesystem $filesystem
     * @param string $debugFile
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param bool $logIndexCheck
     * @param QueryAnalyzerInterface|null $queryAnalyzer
     * @throws FileSystemException
     */
    public function __construct(Filesystem $filesystem, $debug_file = 'debug/db.log', $log_all_queries = false, $log_query_time = 0.05, $log_call_stack = false, $log_index_check = false, ?Query_Analyzer_Interface $query_analyzer = null)
    {
        parent::__construct($log_all_queries, $log_query_time, $log_call_stack, $log_index_check, $query_analyzer);
        $this->dir = $filesystem->get_directory_write(Directory_List::VAR_DIR);
        $this->debug_file = $debug_file;
    }
    /**
     * @inheritDoc
     */
    public function log($str)
    {
        $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str;
        $stream = $this->dir->open_file($this->debug_file, 'a');
        $stream->lock();
        $stream->write($str);
        $stream->unlock();
        $stream->close();
    }
    /**
     * @inheritDoc
     */
    public function log_stats($type, $sql, $bind = [], $result = null)
    {
        $stats = $this->get_stats($type, $sql, $bind, $result);
        if ($stats) {
            $this->log($stats);
        }
    }
    /**
     * @inheritDoc
     */
    public function critical(\Exception $e)
    {
        $this->log("EXCEPTION \n{$e}\n\n");
    }
}