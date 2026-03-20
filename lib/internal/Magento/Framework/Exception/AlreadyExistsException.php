<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
/**
 * @api
 * @since 100.0.2
 */
class Already_Exists_Exception extends Localized_Exception
{
    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Unique constraint violation found');
        }
        parent::__construct($phrase, $cause, $code);
    }
}