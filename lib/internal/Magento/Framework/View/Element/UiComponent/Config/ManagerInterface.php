<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface ManagerInterface
 * @deprecated 101.0.0
 */
interface ManagerInterface
{
    /**
     * Search pattern
     */
    public const SEARCH_PATTERN = '%s.xml';

    /**
     * The anonymous template name
     */
    public const ANONYMOUS_TEMPLATE = 'anonymous_%s_component_%d';

    /**
     * The key arguments in the data component
     */
    public const COMPONENT_ARGUMENTS_KEY = 'arguments';

    /**
     * The key attributes in the data component
     */
    public const COMPONENT_ATTRIBUTES_KEY = 'attributes';

    /**
     * The array key sub components
     */
    public const CHILDREN_KEY = 'children';

    /**
     * Prepare the initialization data of UI components
     *
     * @param string $name
     * @return ManagerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareData($name);

    /**
     * Get component data
     *
     * @param string $name
     * @return array
     */
    public function getData($name);

    /**
     * To create the raw  data components
     *
     * @param string $component
     * @return array
     */
    public function createRawComponentData($component);

    /**
     * Get UIReader and collect base files configuration
     *
     * @param string $name
     * @return UiReaderInterface
     */
    public function getReader($name);
}
