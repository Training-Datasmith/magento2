<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * Class Mapper
 */
class Mapper implements Mapper_Interface
{
    /**
     * @var array
     */
    private $config;
    /**
     * Initialize dependencies.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    /**
     * {@inheritdoc}
     */
    public function entity_to_database($entity_type, $data)
    {
        if (isset($this->config[$entity_type])) {
            foreach ($this->config[$entity_type] as $database_field_name => $entity_field_name) {
                if (!$entity_field_name) {
                    throw new \LogicException('Incorrect configuration for ' . $entity_type);
                }
                if (isset($data[$entity_field_name])) {
                    $data[$database_field_name] = $data[$entity_field_name];
                    unset($data[$entity_field_name]);
                }
            }
        }
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function database_to_entity($entity_type, $data)
    {
        if (isset($this->config[$entity_type])) {
            foreach ($this->config[$entity_type] as $database_field_name => $entity_field_name) {
                if (!$entity_field_name) {
                    throw new \LogicException('Incorrect configuration for ' . $entity_type);
                }
                if (isset($data[$database_field_name])) {
                    $data[$entity_field_name] = $data[$database_field_name];
                    unset($data[$database_field_name]);
                }
            }
        }
        return $data;
    }
}