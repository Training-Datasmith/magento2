<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Select\Select_Renderer;
/**
 * Class SelectFactory
 *
 * @api
 * @since 100.1.0
 */
class Select_Factory
{
    /**
     * @var SelectRenderer
     * @since 100.1.0
     */
    protected $select_renderer;
    /**
     * @var array
     * @since 100.1.0
     */
    protected $parts;
    /**
     * @param SelectRenderer $selectRenderer
     * @param array $parts
     */
    public function __construct(Select_Renderer $select_renderer, $parts = [])
    {
        $this->select_renderer = $select_renderer;
        $this->parts = $parts;
    }
    /**
     * @param AdapterInterface $adapter
     * @return \Magento\Framework\DB\Select
     * @since 100.1.0
     */
    public function create(Adapter_Interface $adapter)
    {
        return new Select($adapter, $this->select_renderer, $this->parts);
    }
}