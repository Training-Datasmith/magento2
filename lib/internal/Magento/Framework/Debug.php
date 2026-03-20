<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

/**
 * Magento Debug methods
 */
class Debug
{
    /**
     * @var int
     */
    public static $arg_length = 16;
    /**
     * Magento Root path
     *
     * @var string
     */
    protected static $_file_path;
    /**
     * Retrieve real root path with last directory separator
     *
     * @return string
     */
    public static function get_root_path()
    {
        if (self::$_file_path === null) {
            if (defined('BP')) {
                self::$_file_path = BP;
            } else {
                self::$_file_path = dirname(__DIR__);
            }
        }
        return self::$_file_path;
    }
    /**
     * Prints or returns a backtrace
     *
     * @param bool $return      return or print
     * @param bool $html        output in HTML format
     * @param bool $withArgs    add short arguments of methods
     * @return string|bool
     */
    public static function backtrace($return = false, $html = true, $with_args = true)
    {
        $trace = debug_backtrace();
        return self::trace($trace, $return, $html, $with_args);
    }
    /**
     * Prints or return a trace
     *
     * @param array $trace      trace array
     * @param bool $return      return or print
     * @param bool $html        output in HTML format
     * @param bool $withArgs    add short arguments of methods
     * @return string|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function trace(array $trace, $return = false, $html = true, $with_args = true)
    {
        $out = '';
        if ($html) {
            $out .= '<pre>';
        }
        foreach ($trace as $i => $data) {
            // skip self
            if ($i == 0) {
                continue;
            }
            // prepare method arguments
            $args = [];
            if (isset($data['args']) && $with_args) {
                foreach ($data['args'] as $arg) {
                    $args[] = self::_format_called_argument($arg);
                }
            }
            // prepare method's name
            if (isset($data['class']) && isset($data['function'])) {
                if (isset($data['object']) && get_class($data['object']) != $data['class']) {
                    $class_name = get_class($data['object']) . '[' . $data['class'] . ']';
                } else {
                    $class_name = $data['class'];
                }
                if (isset($data['object'])) {
                    $class_name .= sprintf('#%s#', spl_object_hash($data['object']));
                }
                $method_name = sprintf('%s%s%s(%s)', $class_name, isset($data['type']) ? $data['type'] : '->', $data['function'], join(', ', $args));
            } elseif (isset($data['function'])) {
                $method_name = sprintf('%s(%s)', $data['function'], join(', ', $args));
            }
            if (isset($data['file'])) {
                $pos = strpos($data['file'], self::get_root_path());
                if ($pos !== false) {
                    $data['file'] = substr($data['file'], strlen(self::get_root_path()) + 1);
                }
                $file_name = sprintf('%s:%d', $data['file'], $data['line']);
            } else {
                $file_name = false;
            }
            if ($file_name) {
                $out .= sprintf('#%d %s called at [%s]', $i, $method_name, $file_name);
            } else {
                $out .= sprintf('#%d %s', $i, $method_name);
            }
            $out .= "\n";
        }
        if ($html) {
            $out .= '</pre>';
        }
        if ($return) {
            return $out;
        } else {
            echo $out;
            return true;
        }
    }
    /**
     * Format argument in called method
     *
     * @param mixed $arg
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function _format_called_argument($arg)
    {
        $out = '';
        if (is_object($arg)) {
            $out .= sprintf('&%s#%s#', get_class($arg), spl_object_hash($arg));
        } elseif (is_resource($arg)) {
            $out .= '#[' . get_resource_type($arg) . ']';
        } elseif (is_array($arg)) {
            $is_associative = false;
            $args = [];
            foreach ($arg as $k => $v) {
                if (!is_numeric($k)) {
                    $is_associative = true;
                }
                $args[$k] = self::_format_called_argument($v);
            }
            if ($is_associative) {
                $arr = [];
                foreach ($args as $k => $v) {
                    $arr[] = self::_format_called_argument($k) . ' => ' . $v;
                }
                $out .= 'array(' . join(', ', $arr) . ')';
            } else {
                $out .= 'array(' . join(', ', $args) . ')';
            }
        } elseif ($arg === null) {
            $out .= 'NULL';
        } elseif (is_numeric($arg) || is_float($arg)) {
            $out .= $arg;
        } elseif (is_string($arg)) {
            if (strlen($arg) > self::$arg_length) {
                $arg = substr($arg, 0, self::$arg_length) . '...';
            }
            $arg = strtr($arg, ["\t" => '\t', "\r" => '\r', "\n" => '\n', "'" => '\\\'']);
            $out .= "'" . $arg . "'";
        } elseif (is_bool($arg)) {
            $out .= $arg === true ? 'true' : 'false';
        }
        return $out;
    }
}