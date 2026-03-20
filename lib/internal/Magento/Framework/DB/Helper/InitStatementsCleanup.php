<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\DB\Helper;

/**
 * Helper class to remove deprecated 'SET NAMES utf8;' from database connection initStatements
 *
 * This helper provides methods to clean up deprecated 'SET NAMES utf8;' statements from
 * database connection configurations in env.php during setup/upgrade operations.
 */
class Init_Statements_Cleanup
{
    /**
     * Remove 'SET NAMES utf8;' from initStatements string
     *
     * Rules:
     * - If initStatements contains only 'SET NAMES utf8;' or 'SET NAMES utf8', returns null
     *   (indicating the entire initStatements key should be removed)
     * - If initStatements contains 'SET NAMES utf8;' along with other statements,
     *   removes only 'SET NAMES utf8;' and returns the cleaned string
     *
     * @param string $initStatements The initStatements string from connection config
     * @return string|null Returns cleaned string, or null if nothing remains
     */
    public function remove_set_names_utf8(string $init_statements): ?string
    {
        // Skip if empty
        if (empty(trim($init_statements))) {
            return null;
        }
        // Check if it contains 'SET NAMES utf8'
        if (stripos($init_statements, 'SET NAMES utf8') === false) {
            return $init_statements;
        }
        // Remove 'SET NAMES utf8;' and 'SET NAMES utf8' (with or without semicolon)
        $cleaned = preg_replace('/\s*SET\s+NAMES\s+utf8\s*;?\s*/i', '', $init_statements);
        // Clean up any extra semicolons or whitespace
        $cleaned = trim($cleaned);
        $cleaned = preg_replace('/;\s*;+/', ';', $cleaned);
        // Remove duplicate semicolons
        $cleaned = trim($cleaned, ';');
        // Remove leading/trailing semicolons
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        // Normalize whitespace
        // If nothing left after removing SET NAMES utf8, return null
        if (empty($cleaned)) {
            return null;
        }
        // Return cleaned value with semicolon at the end
        return $cleaned . ';';
    }
    /**
     * Process connection configuration array and remove deprecated SET NAMES utf8
     *
     * This method modifies the connection config array in-place.
     * - Removes entire 'initStatements' key if it only contains 'SET NAMES utf8;'
     * - Updates 'initStatements' with cleaned value if it contains other statements
     *
     * @param array &$connectionConfig Database connection configuration array (passed by reference)
     * @return bool True if configuration was modified
     */
    public function process_connection_config(array &$connection_config): bool
    {
        if (!isset($connection_config['initStatements'])) {
            return false;
        }
        $init_statements = $connection_config['initStatements'];
        // Skip if not a string
        if (!is_string($init_statements)) {
            return false;
        }
        $cleaned = $this->remove_set_names_utf8($init_statements);
        // If cleaned is null, remove the entire initStatements key
        if ($cleaned === null) {
            unset($connection_config['initStatements']);
            return true;
        }
        // If cleaned is different from original, update it
        if ($cleaned !== $init_statements) {
            $connection_config['initStatements'] = $cleaned;
            return true;
        }
        return false;
    }
    /**
     * Process all database connections in env.php config array
     *
     * This method processes both regular connections (db/connection) and slave connections
     * (db/slave_connection) to remove deprecated 'SET NAMES utf8;' statements.
     *
     * @param array &$envConfig The full env.php configuration array (passed by reference)
     * @return bool True if any configuration was modified
     */
    public function process_env_config(array &$env_config): bool
    {
        $modified = false;
        // Process regular database connections
        if (isset($env_config['db']['connection']) && is_array($env_config['db']['connection'])) {
            foreach ($env_config['db']['connection'] as &$connection_config) {
                if ($this->process_connection_config($connection_config)) {
                    $modified = true;
                }
            }
            unset($connection_config);
            // Break reference
        }
        // Process slave connections
        if (isset($env_config['db']['slave_connection']) && is_array($env_config['db']['slave_connection'])) {
            foreach ($env_config['db']['slave_connection'] as &$slave_config) {
                if ($this->process_connection_config($slave_config)) {
                    $modified = true;
                }
            }
            unset($slave_config);
            // Break reference
        }
        return $modified;
    }
}