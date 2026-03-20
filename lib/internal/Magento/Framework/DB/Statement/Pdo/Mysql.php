<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Statement\Pdo;

use Magento\Framework\DB\Statement\Parameter;
/**
 * Mysql DB Statement
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mysql extends \Zend_Db_Statement_Pdo
{
    /**
     * Executes statement with binding values to it. Allows transferring specific options to DB driver.
     *
     * @param array $params Array of values to bind to parameter placeholders.
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function _execute_with_binding(array $params)
    {
        // Check whether we deal with named bind
        $is_positional_bind = true;
        foreach ($params as $k => $v) {
            if (!is_int($k)) {
                $is_positional_bind = false;
                break;
            }
        }
        /* @var $statement \PDOStatement */
        $statement = $this->_stmt;
        $bind_values = [];
        // Separate array with values, as they are bound by reference
        foreach ($params as $name => $param) {
            $data_type = \PDO::PARAM_STR;
            $length = is_string($param) ? strlen($param) : 0;
            $driver_options = null;
            if ($param instanceof Parameter) {
                if (!$param->get_is_blob()) {
                    $data_type = $param->get_data_type();
                    $length = $param->get_length();
                    $driver_options = $param->get_driver_options();
                }
                $bind_values[$name] = $param->get_value();
            } else {
                $bind_values[$name] = $param;
            }
            $param_name = $is_positional_bind ? $name + 1 : $name;
            $statement->bind_param($param_name, $bind_values[$name], $data_type, $length, $driver_options);
        }
        return $this->try_execute(function () use ($statement) {
            return $statement->execute();
        });
    }
    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function _execute(?array $params = null)
    {
        $special_execute = false;
        if ($params) {
            foreach ($params as $param) {
                if ($param instanceof Parameter) {
                    $special_execute = true;
                    break;
                }
            }
        }
        if ($special_execute) {
            return $this->_execute_with_binding($params);
        } else {
            return $this->try_execute(function () use ($params) {
                return !empty($params) ? $this->_stmt->execute($params) : $this->_stmt->execute();
            });
        }
    }
    /**
     * Executes query and avoid warnings.
     *
     * @param callable $callback
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    private function try_execute($callback)
    {
        $previous_level = error_reporting(\E_ERROR);
        // disable warnings for PDO bugs #63812, #74401
        try {
            return $callback();
        } catch (\PDOException $e) {
            $message = sprintf('%s, query was: %s', $e->get_message(), $this->_stmt->query_string);
            throw new \Zend_Db_Statement_Exception($message, (int) $e->get_code(), $e);
        } finally {
            error_reporting($previous_level);
        }
    }
}