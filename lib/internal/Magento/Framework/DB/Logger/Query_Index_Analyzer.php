<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Logger;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\Serialize\Serializer\Json;
class Query_Index_Analyzer implements Query_Analyzer_Interface
{
    private const DEFAULT_SMALL_TABLE_THRESHOLD = 100;
    /**
     * @var int
     */
    private int $small_table_threshold;
    /**
     * @var array
     */
    private array $analyzer_cache = [];
    /**
     * @param ResourceConnection $resource
     * @param Json $serializer
     * @param int|null $smallTableThreshold
     */
    public function __construct(private readonly Resource_Connection $resource, private readonly Json $serializer, ?int $small_table_threshold = null)
    {
        $this->small_table_threshold = (int) $small_table_threshold > 0 ? (int) $small_table_threshold : self::DEFAULT_SMALL_TABLE_THRESHOLD;
    }
    /**
     * Check for potential index issues
     *
     * @param string $sql
     * @param array $bindings
     * @return array
     * @throws \Zend_Db_Statement_Exception|QueryAnalyzerException
     */
    public function process(string $sql, array $bindings): array
    {
        if (!$this->is_select_query($sql)) {
            throw new Query_Analyzer_Exception("Can't process query type");
        }
        $cache_key = $this->generate_cache_key($sql, $bindings);
        if (isset($this->analyzer_cache[$cache_key])) {
            $explain_output = $this->analyzer_cache[$cache_key];
        } else {
            $connection = $this->resource->get_connection();
            try {
                $explain_output = $connection->query('EXPLAIN ' . $sql, $bindings)->fetch_all();
            } catch (\Zend_Db_Adapter_Exception) {
                $explain_output = [];
            }
            $this->analyzer_cache[$cache_key] = $explain_output;
        }
        if (empty($explain_output)) {
            throw new Query_Analyzer_Exception("No 'explain' output available");
        }
        $issues = $this->analyze_queries($explain_output);
        if ($issues === null) {
            throw new Query_Analyzer_Exception('Small table');
        }
        return array_values(array_unique($issues));
    }
    /**
     * Generate a cache key based on the SQL query and its bindings.
     *
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    private function generate_cache_key(string $sql, array $bindings): string
    {
        return base64_encode(hash('sha256', $sql . '|' . $this->serializer->serialize($bindings), true));
    }
    /**
     * Detects if a given SQL string is a SELECT query.
     *
     * @param string $query
     * @return bool
     */
    private function is_select_query(string $query): bool
    {
        $cleaned = ltrim($query);
        // Remove leading SQL line comments (e.g., -- comment) and block comments (/* ... */)
        while (preg_match('/^(--[^\n]*\n|\/\*.*?\*\/\s*)/s', $cleaned, $matches)) {
            $cleaned = ltrim(substr($cleaned, strlen($matches[0])));
        }
        // Check if the cleaned string starts with SELECT (case-insensitive)
        return (bool) preg_match('/^SELECT\b/i', $cleaned);
    }
    /**
     * Check each select from given query for potential issues
     *
     * @param array $explainOutput
     * @return array|null
     */
    private function analyze_queries(array $explain_output): ?array
    {
        $issues = array_map(fn(array $row) => $this->get_query_issues($row), $explain_output);
        if (!array_filter($issues, 'is_array')) {
            return null;
        }
        return array_merge(...array_filter($issues));
    }
    /**
     * Check EXPLAIN output for potential issues
     *
     * @param array $selectDetails
     * @return array|null
     */
    private function get_query_issues(array $select_details): ?array
    {
        $issues = [];
        $select_details = array_change_key_case($select_details);
        $type = strtolower($select_details['type'] ?? '');
        // skip small tables
        if ((int) $select_details['rows'] < $this->small_table_threshold && $type === 'all') {
            return null;
        }
        if ($this->has_full_table_scan($select_details)) {
            $issues[] = self::FULL_TABLE_SCAN;
        }
        if (false === $this->is_using_index($select_details)) {
            $issues[] = self::NO_INDEX;
        }
        if ($this->is_using_file_sort($select_details)) {
            $issues[] = self::FILESORT;
        }
        if ($this->has_dependent_subquery($select_details)) {
            $issues[] = self::DEPENDENT_SUBQUERY;
        }
        if ($this->is_partial_index_usage($select_details)) {
            $issues[] = self::PARTIAL_INDEX;
        }
        return $issues;
    }
    /**
     * Check if dependent subqueries are used
     *
     * @param array $selectDetails
     * @return bool
     */
    private function has_dependent_subquery(array $select_details): bool
    {
        $select_type = strtolower($select_details['select_type'] ?? '');
        return $select_type === 'dependent subquery';
    }
    /**
     * Check if query is using filesort
     *
     * @param array $selectDetails
     * @return bool
     */
    private function is_using_file_sort(array $select_details): bool
    {
        $extra = strtolower($select_details['extra'] ?? '');
        return str_contains($extra, 'using filesort');
    }
    /**
     * Check if query optimizer is using an index
     *
     * @param array $selectDetails
     * @return bool
     */
    private function is_using_index(array $select_details): bool
    {
        $extra = strtolower($select_details['extra'] ?? '');
        $key = $select_details['key'] ?? null;
        return !(empty($key) && !str_contains($extra, 'no matching row in const table'));
    }
    /**
     * Check if query uses full table scan
     *
     * @param array $selectDetails
     * @return bool
     */
    private function has_full_table_scan(array $select_details): bool
    {
        $key = $select_details['key'] ?? null;
        $type = $select_details['type'] ?? '';
        return strtolower($type) === 'all' && empty($key);
    }
    /**
     * Check for partial index usage
     *
     * @param array $row
     * @return bool
     */
    private function is_partial_index_usage(array $row): bool
    {
        $extra = strtolower($row['extra'] ?? '');
        $type = strtolower($row['type'] ?? '');
        $key = $row['key'] ?? '';
        if (empty($key)) {
            return false;
        }
        if ($this->check_for_covering_index($extra, $type)) {
            return false;
        }
        if ($this->check_efficient_access_types($type)) {
            return false;
        }
        // Partial usage: index used but not covering, or used inefficiently
        if (str_contains($extra, 'using filesort') || str_contains($extra, 'using temporary') || $type === 'index' && !str_contains($extra, 'using index')) {
            return true;
        }
        return false;
    }
    /**
     * Check for clues over covering index
     *
     * @param string $extra
     * @param string $type
     * @return bool
     */
    private function check_for_covering_index(string $extra, string $type): bool
    {
        return str_contains($extra, 'using index') && !str_contains($extra, 'using where') || str_contains($extra, 'using index') && str_contains($extra, 'using where') && in_array($type, ['range', 'ref']);
    }
    /**
     * Check if query is using an efficient access type
     *
     * @param string $type
     * @return bool
     */
    private function check_efficient_access_types(string $type): bool
    {
        return in_array($type, ['const', 'eq_ref']);
    }
}