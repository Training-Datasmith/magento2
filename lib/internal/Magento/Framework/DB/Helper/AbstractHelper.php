<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Helper;

/**
 * Abstract DB helper class
 */
abstract class Abstract_Helper
{
    /**
     * Resource helper module prefix
     *
     * @var string
     */
    protected $_module_prefix;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * Initialize resource helper instance
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource_Connection $resource, $module_prefix)
    {
        $this->_resource = $resource;
        $this->_module_prefix = (string) $module_prefix;
    }
    /**
     * Retrieves connection to the resource
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function get_connection()
    {
        return $this->_resource->get_connection($this->_module_prefix);
    }
    /**
     * Escapes value, that participates in LIKE, with '\' symbol.
     * Note: this func cannot be used on its own, because different RDMBS may use different default escape symbols,
     * so you should either use addLikeEscape() to produce LIKE construction, or add escape symbol on your own.
     *
     * By default escapes '_', '%' and '\' symbols. If some masking symbols must not be escaped, then you can set
     * appropriate options in $options.
     *
     * $options can contain following flags:
     * - 'allow_symbol_mask' - the '_' symbol will not be escaped
     * - 'allow_string_mask' - the '%' symbol will not be escaped
     * - 'position' ('any', 'start', 'end') - expression will be formed so that $value will be found at position
     *      within string, by default when nothing set - string must be fully matched with $value
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public function escape_like_value($value, $options = [])
    {
        $value = $value !== null ? str_replace('\\', '\\\\', $value) : '';
        $replace_from = [];
        $replace_to = [];
        if (empty($options['allow_symbol_mask'])) {
            $replace_from[] = '_';
            $replace_to[] = '\_';
        }
        if (empty($options['allow_string_mask'])) {
            $replace_from[] = '%';
            $replace_to[] = '\%';
        }
        if ($replace_from) {
            $value = str_replace($replace_from, $replace_to, $value);
        }
        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%' . $value . '%';
                    break;
                case 'start':
                    $value = $value . '%';
                    break;
                case 'end':
                    $value = '%' . $value;
                    break;
                default:
                    break;
            }
        }
        return $value;
    }
    /**
     * Escapes, quotes and adds escape symbol to LIKE expression. For options and escaping see escapeLikeValue().
     *
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    abstract public function add_like_escape($value, $options = []);
    /**
     * Returns case insensitive LIKE construction. For options and escaping see escapeLikeValue().
     *
     * @param string $field
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    public function get_ci_like($field, $value, $options = [])
    {
        $quoted_field = $this->get_connection()->quote_identifier($field);
        return new \Zend_Db_Expr($quoted_field . ' LIKE ' . $this->add_like_escape($value, $options));
    }
}