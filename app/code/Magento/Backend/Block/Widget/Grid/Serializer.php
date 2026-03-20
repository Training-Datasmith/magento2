<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @method string|array getInputNames()
 * @since 100.0.2
 */
class Serializer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_json_encoder;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, array $data = [])
    {
        $this->_json_encoder = $json_encoder;
        parent::__construct($context, $data);
    }
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $grid = $this->get_grid_block();
        if (is_string($grid)) {
            $grid = $this->get_layout()->get_block($grid);
        }
        if ($grid instanceof \Magento\Backend\Block\Widget\Grid) {
            $this->set_grid_block($grid)->set_serialize_data($grid->{$this->get_callback()}());
        }
        return parent::_prepare_layout();
    }
    /**
     * Set serializer template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->set_template('Magento_Backend::widget/grid/serializer.phtml');
    }
    /**
     * Get grid column input names to serialize
     *
     * @param bool $asJSON
     * @return string|array
     */
    public function get_column_input_names($as_json = false)
    {
        if ($as_json) {
            return $this->_json_encoder->encode((array) $this->get_input_names());
        }
        return (array) $this->get_input_names();
    }
    /**
     * Get object data as JSON
     *
     * @return string
     */
    public function get_data_as_json()
    {
        $result = [];
        $input_names = $this->get_input_names();
        if ($serialize_data = $this->get_serialize_data()) {
            $result = $serialize_data;
        } elseif (!empty($input_names)) {
            return '{}';
        }
        return $this->_json_encoder->encode($result);
    }
}