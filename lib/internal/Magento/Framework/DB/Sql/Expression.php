<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Sql;

/**
 * Class is wrapper over Zend_Db_Expr for implement JsonSerializable interface.
 */
class Expression extends \Zend_Db_Expr implements Expression_Interface, \JsonSerializable
{
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return ['class' => static::class, 'arguments' => ['expression' => $this->_expression]];
    }
}