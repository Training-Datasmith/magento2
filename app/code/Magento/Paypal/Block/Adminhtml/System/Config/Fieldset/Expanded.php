<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

/**
 * Fieldset renderer which expanded by default
 */
class Expanded extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Whether is collapsed by default
     *
     * @var bool
     */
    protected $isCollapsedDefault = true;
}
