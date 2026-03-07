<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Serialization Exception
 *
 * @api
 * @since 100.0.2
 */
class SerializationException extends LocalizedException
{
    /**
     * @deprecated
     */
    public const DEFAULT_MESSAGE = 'Invalid type';

    /**
     * @deprecated
     */
    public const TYPE_MISMATCH = 'The "%value" value\'s type is invalid. The "%type" type was expected. Verify and try again.';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('One or more input exceptions have occurred.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
