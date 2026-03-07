<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App;

/**
 * Interface AreaInterface
 *
 * @api
 */
interface AreaInterface
{
    public const PART_CONFIG = 'config';
    public const PART_TRANSLATE = 'translate';
    public const PART_DESIGN = 'design';

    /**
     * Load area part
     *
     * @param string $partName
     * @return $this
     */
    public function load($partName = null);
}
