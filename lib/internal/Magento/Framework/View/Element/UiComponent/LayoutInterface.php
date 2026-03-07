<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface LayoutInterface
 *
 * @api
 */
interface LayoutInterface
{
    public const SECTIONS_KEY = 'sections';

    public const AREAS_KEY = 'areas';

    public const GROUPS_KEY = 'groups';

    public const ELEMENTS_KEY = 'elements';

    public const DATA_SOURCE_KEY = 'data_source';

    /**
     * @param UiComponentInterface $component
     * @return array
     */
    public function build(UiComponentInterface $component);
}
