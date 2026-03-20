<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
/**
 * Phrase renderer interface
 */
class Select_Renderer implements Renderer_Interface
{
    private const MANDATORY_SELECT_PARTS = [Select::COLUMNS => true, Select::FROM => true];
    /**
     * @var RendererInterface[]
     */
    protected $renderers;
    /**
     * @param RendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = $this->sort($renderers);
    }
    /**
     * Sort renderers
     *
     * @param array $renders
     * @return array
     */
    protected function sort($renders)
    {
        $length = count($renders);
        if ($length <= 1) {
            return $renders;
        } else {
            $pivot = array_shift($renders);
            $left = $right = [];
            foreach ($renders as $render) {
                if ($render['sort'] < $pivot['sort']) {
                    $left[] = $render;
                } else {
                    $right[] = $render;
                }
            }
            return array_merge($this->sort($left), [$pivot], $this->sort($right));
        }
    }
    /**
     * Render SELECT statement
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        $sql = Select::SQL_SELECT;
        foreach ($this->renderers as $renderer) {
            $part = $renderer['part'];
            if (isset(self::MANDATORY_SELECT_PARTS[$part]) || $select->get_part($part)) {
                $sql = $renderer['renderer']->render($select, $sql);
            }
        }
        return $sql;
    }
}