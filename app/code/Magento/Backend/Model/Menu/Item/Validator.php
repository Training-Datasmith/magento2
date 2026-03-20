<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Item;

use Laminas\Validator\Regex;
use Laminas\Validator\Validator_Chain;
use Magento\Framework\Validator\String_Length;
/**
 * @api
 * @since 100.0.2
 */
class Validator
{
    /**
     * The list of required params
     *
     * @var string[]
     */
    protected $_required = ['id', 'title', 'resource'];
    /**
     * List of created item ids
     *
     * @var array
     */
    protected $_ids = [];
    /**
     * The list of primitive validators
     *
     * @var ValidatorChain[]
     */
    protected $_validators = [];
    /**
     * Constructor
     */
    public function __construct()
    {
        $id_validator = new Validator_Chain();
        $id_validator->add_validator(new String_Length(['min' => 3]));
        $id_validator->add_validator(new Regex('/^[A-Za-z0-9\/:_]+$/'));
        $resource_validator = new Validator_Chain();
        $resource_validator->add_validator(new String_Length(['min' => 8]));
        $resource_validator->add_validator(new Regex('/^[A-Z][A-Za-z0-9]+_[A-Z][A-Za-z0-9]+::[A-Za-z_0-9]+$/'));
        $attribute_validator = new Validator_Chain();
        $attribute_validator->add_validator(new String_Length(['min' => 3]));
        $attribute_validator->add_validator(new Regex('/^[A-Za-z0-9\/_\-]+$/'));
        $text_validator = new String_Length(['min' => 3, 'max' => 50]);
        $title_validator = $tooltip_validator = $text_validator;
        $action_validator = $module_dep_validator = $config_dep_validator = $attribute_validator;
        $this->_validators['id'] = $id_validator;
        $this->_validators['title'] = $title_validator;
        $this->_validators['action'] = $action_validator;
        $this->_validators['resource'] = $resource_validator;
        $this->_validators['dependsOnModule'] = $module_dep_validator;
        $this->_validators['dependsOnConfig'] = $config_dep_validator;
        $this->_validators['toolTip'] = $tooltip_validator;
    }
    /**
     * Validate menu item params
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function validate($data)
    {
        if ($this->check_menu_item_is_removed($data)) {
            return;
        }
        $this->assert_contains_required_parameters($data);
        $this->assert_identifier_is_not_used($data['id']);
        foreach ($data as $param => $value) {
            $this->validate_menu_item_parameter($param, $value);
        }
        $this->_ids[] = $data['id'];
    }
    /**
     * Check that menu item is not deleted
     *
     * @param array $data
     * @return bool
     */
    private function check_menu_item_is_removed($data)
    {
        return isset($data['id'], $data['removed']) && $data['removed'] === true;
    }
    /**
     * Check that menu item contains all required data
     *
     * @param array $data
     *
     * @throws \BadMethodCallException
     */
    private function assert_contains_required_parameters($data)
    {
        foreach ($this->_required as $param) {
            if (!isset($data[$param])) {
                throw new \BadMethodCallException('Missing required param ' . $param);
            }
        }
    }
    /**
     * Check that menu item id is not used
     *
     * @param string $id
     * @throws \InvalidArgumentException
     */
    private function assert_identifier_is_not_used($id)
    {
        if (array_search($id, $this->_ids) !== false) {
            throw new \InvalidArgumentException('Item with id ' . $id . ' already exists');
        }
    }
    /**
     * Validate menu item parameter value
     *
     * @param string $param
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    private function validate_menu_item_parameter($param, $value)
    {
        if ($value === null) {
            return;
        }
        if (!isset($this->_validators[$param])) {
            return;
        }
        $validator = $this->_validators[$param];
        if ($validator->is_valid($value)) {
            return;
        }
        throw new \InvalidArgumentException('Param ' . $param . " doesn't pass validation: " . implode('; ', $validator->get_messages()));
    }
    /**
     * Validate incoming param
     *
     * @param string $param
     * @param mixed $value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validate_param($param, $value)
    {
        if (in_array($param, $this->_required) && $value === null) {
            throw new \InvalidArgumentException('Param ' . $param . ' is required');
        }
        if ($value !== null && isset($this->_validators[$param]) && !$this->_validators[$param]->is_valid($value)) {
            throw new \InvalidArgumentException('Param ' . $param . ' doesn\'t pass validation: ' . implode('; ', $this->_validators[$param]->get_messages()));
        }
    }
}