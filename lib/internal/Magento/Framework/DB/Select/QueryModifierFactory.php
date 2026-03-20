<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\Object_Manager_Interface;
/**
 * Create instance of QueryModifierInterface
 */
class Query_Modifier_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var array
     */
    private $query_modifiers;
    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $queryModifiers
     */
    public function __construct(Object_Manager_Interface $object_manager, array $query_modifiers = [])
    {
        $this->object_manager = $object_manager;
        $this->query_modifiers = $query_modifiers;
    }
    /**
     * Create instance of QueryModifierInterface
     *
     * @param string $type
     * @param array $data
     * @return QueryModifierInterface
     * @throws \InvalidArgumentException
     */
    public function create($type, array $data = [])
    {
        if (!isset($this->query_modifiers[$type])) {
            throw new \InvalidArgumentException('Unknown query modifier type ' . $type);
        }
        $query_modifier = $this->object_manager->create($this->query_modifiers[$type], $data);
        if (!$query_modifier instanceof Query_Modifier_Interface) {
            throw new \InvalidArgumentException($this->query_modifiers[$type] . ' must implement ' . Query_Modifier_Interface::class);
        }
        return $query_modifier;
    }
}