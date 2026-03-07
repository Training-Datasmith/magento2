<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CheckoutAgreements\Model;

class AgreementModeOptions
{
    public const MODE_AUTO = 0;

    public const MODE_MANUAL = 1;

    /**
     * Return list of agreement mode options array.
     *
     * @return array
     */
    public function getOptionsArray()
    {
        return [
            self::MODE_AUTO => __('Automatically'),
            self::MODE_MANUAL => __('Manually'),
        ];
    }
}
