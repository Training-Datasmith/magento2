<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;

/**
 * Fieldset UI Component.
 *
 * @api
 * @since 100.0.2
 */
class Fieldset extends AbstractComponent
{
    public const NAME = 'fieldset';

    /**
     * @var bool
     */
    protected $collapsible = false;

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
