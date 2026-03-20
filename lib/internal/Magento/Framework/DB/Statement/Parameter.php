<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Statement;

/**
 * Magento DB Statement Parameter
 *
 * Used to transmit specific information about parameter value binding to be bound the right
 * way to the query.
 * Most used properties and methods are defined in interface. Specific things for concrete DB adapter can be
 * transmitted using 'addtional' property (\Magento\Framework\DataObject) as a container.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Parameter
{
    /**
     * Actual parameter value
     *
     * @var mixed
     */
    protected $_value = null;
    /**
     * Value is a BLOB.
     *
     * A shortcut setting to notify DB adapter, that value must be bound in a default way, as adapter binds
     * BLOB data to query placeholders. If FALSE, then specific settings from $_dataType, $_length,
     * $_driverOptions will be used.
     * @var bool
     */
    protected $_is_blob = false;
    /**
     * Data type to set to DB driver during parameter bind
     * @var mixed
     */
    protected $_data_type = null;
    /**
     * Length to set to DB driver during parameter bind
     * @var mixed
     */
    protected $_length = null;
    /**
     * Specific driver options to set to DB driver during parameter bind
     * @var mixed
     */
    protected $_driver_options = null;
    /**
     * Additional information to be used by DB adapter internally
     * @var \Magento\Framework\DataObject
     */
    protected $_additional = null;
    /**
     * Inits instance
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->_value = $value;
        $this->_additional = new \Magento\Framework\Data_Object();
        return $this;
    }
    /**
     * Sets parameter value.
     *
     * @param mixed $value
     * @return $this
     */
    public function set_value($value)
    {
        $this->_value = $value;
        return $this;
    }
    /**
     * Gets parameter value.
     *
     * @return mixed
     */
    public function get_value()
    {
        return $this->_value;
    }
    /**
     * Sets, whether parameter is a BLOB.
     *
     * FALSE (default) means, that concrete binding options come in dataType, length and driverOptions properties.
     * TRUE means that DB adapter must ignore other options and use adapter's default options to bind this parameter
     * as a BLOB value.
     *
     * @param bool $isBlob
     * @return $this
     */
    public function set_is_blob($is_blob)
    {
        $this->_is_blob = $is_blob;
        return $this;
    }
    /**
     * Gets, whether parameter is a BLOB.
     * See setIsBlob() for returned value explanation.
     *
     * @return bool
     *
     * @see setIsBlob
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_is_blob()
    {
        return $this->_is_blob;
    }
    /**
     * Sets data type option to be used during binding parameter value.
     *
     * @param mixed $dataType
     * @return $this
     */
    public function set_data_type($data_type)
    {
        $this->_data_type = $data_type;
        return $this;
    }
    /**
     * Gets data type option to be used during binding parameter value.
     *
     * @return mixed
     */
    public function get_data_type()
    {
        return $this->_data_type;
    }
    /**
     * Sets length option to be used during binding parameter value.
     *
     * @param mixed $length
     * @return $this
     */
    public function set_length($length)
    {
        $this->_length = $length;
        return $this;
    }
    /**
     * Gets length option to be used during binding parameter value.
     *
     * @return mixed
     */
    public function get_length()
    {
        return $this->_length;
    }
    /**
     * Sets specific driver options to be used during binding parameter value.
     *
     * @param mixed $driverOptions
     * @return $this
     */
    public function set_driver_options($driver_options)
    {
        $this->_driver_options = $driver_options;
        return $this;
    }
    /**
     * Gets driver options to be used during binding parameter value.
     *
     * @return mixed
     */
    public function get_driver_options()
    {
        return $this->_driver_options;
    }
    /**
     * Sets additional information for concrete DB adapter.
     * Set there any data you want to pass along with query parameter.
     *
     * @param \Magento\Framework\DataObject $additional
     * @return $this
     */
    public function set_additional($additional)
    {
        $this->_additional = $additional;
        return $this;
    }
    /**
     * Gets additional information for concrete DB adapter.
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_additional()
    {
        return $this->_additional;
    }
    /**
     * Returns representation of a object to be used in string contexts
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_value;
    }
    /**
     * Returns representation of a object to be used in string contexts
     *
     * @return string
     */
    public function to_string()
    {
        return $this->__toString();
    }
}