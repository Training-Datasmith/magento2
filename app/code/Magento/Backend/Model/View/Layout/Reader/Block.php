<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\View\Layout\Reader;

use Magento\Framework\Data\Argument\Interpreter_Interface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;
/**
 * Backend block structure reader with ACL support
 * @api
 * @since 100.0.2
 */
class Block extends Layout\Reader\Block
{
    /**
     * Initialize dependencies.
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param Condition $conditionReader
     * @param string|null $scopeType
     */
    public function __construct(Layout\Scheduled_Structure\Helper $helper, Layout\Argument\Parser $argument_parser, Layout\Reader_Pool $reader_pool, Interpreter_Interface $argument_interpreter, Condition $condition_reader, $scope_type = null)
    {
        $this->attributes[] = 'acl';
        parent::__construct($helper, $argument_parser, $reader_pool, $argument_interpreter, $condition_reader, $scope_type);
    }
}