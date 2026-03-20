<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager\Sequence;

use Magento\Framework\DB\Sequence\Sequence_Interface;
/**
 * Class SequenceRegistry
 */
class Sequence_Registry
{
    /**
     * @var array
     */
    private $registry;
    /**
     * Register information about existing sequence
     *
     * @param string $entityType
     * @param SequenceInterface|null $sequence
     * @param string|null $sequenceTable
     * @return void
     */
    public function register($entity_type, $sequence = null, $sequence_table = null)
    {
        $this->registry[$entity_type]['sequence'] = $sequence;
        $this->registry[$entity_type]['sequenceTable'] = $sequence_table;
    }
    /**
     * Returns sequence information
     *
     * @param string $entityType
     * @return bool|array
     */
    public function retrieve($entity_type)
    {
        if (isset($this->registry[$entity_type])) {
            return $this->registry[$entity_type];
        }
        return false;
    }
}