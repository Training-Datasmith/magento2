<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * Grid row url generator
 * @api
 * @since 100.0.2
 */
class Url_Generator implements \Magento\Backend\Model\Widget\Grid\Row\Generator_Interface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url_model;
    /**
     * @var string
     */
    protected $_path;
    /**
     * @var array
     */
    protected $_params = [];
    /**
     * @var array
     */
    protected $_extra_params_template = [];
    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $args
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Backend\Model\Url_Interface $backend_url, array $args = [])
    {
        if (!isset($args['path'])) {
            throw new \InvalidArgumentException('Not all required parameters passed');
        }
        $this->_url_model = isset($args['urlModel']) ? $args['urlModel'] : $backend_url;
        $this->_path = (string) $args['path'];
        if (isset($args['params'])) {
            $this->_params = (array) $args['params'];
        }
        if (isset($args['extraParamsTemplate'])) {
            $this->_extra_params_template = (array) $args['extraParamsTemplate'];
        }
    }
    /**
     * Create url for passed item using passed url model
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function get_url($item)
    {
        if (!empty($this->_path)) {
            $params = $this->_prepare_parameters($item);
            return $this->_url_model->get_url($this->_path, $params);
        }
        return '';
    }
    /**
     * Convert template params array and merge with preselected params
     *
     * @param \Magento\Framework\DataObject $item
     * @return array
     */
    protected function _prepare_parameters($item)
    {
        $params = [];
        foreach ($this->_extra_params_template as $param_key => $param_value_method) {
            $params[$param_key] = $item->{$param_value_method}();
        }
        return array_merge($this->_params, $params);
    }
}