<?php

declare(strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogRule\Api\Data;

/**
 * @api
 * @since 100.1.0
 */
interface ConditionInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const TYPE = 'type';

    public const ATTRIBUTE = 'attribute';

    public const OPERATOR = 'operator';

    public const VALUE = 'value';

    public const IS_VALUE_PARSED = 'is_value_parsed';

    public const AGGREGATOR = 'aggregator';

    public const CONDITIONS = 'conditions';
    /**#@-*/

    /**
     * @param string $type
     * @return $this
     * @since 100.1.0
     */
    public function setType($type);

    /**
     * @return string
     * @since 100.1.0
     */
    public function getType();

    /**
     * @param string $attribute
     * @return $this
     * @since 100.1.0
     */
    public function setAttribute($attribute);

    /**
     * @return string
     * @since 100.1.0
     */
    public function getAttribute();

    /**
     * @param string $operator
     * @return $this
     * @since 100.1.0
     */
    public function setOperator($operator);

    /**
     * @return string
     * @since 100.1.0
     */
    public function getOperator();

    /**
     * @param string $value
     * @return $this
     * @since 100.1.0
     */
    public function setValue($value);

    /**
     * @return string
     * @since 100.1.0
     */
    public function getValue();

    /**
     * @param bool $isValueParsed
     * @return $this
     * @since 100.1.0
     */
    public function setIsValueParsed($isValueParsed);

    /**
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 100.1.0
     */
    public function getIsValueParsed();

    /**
     * @param string $aggregator
     * @return $this
     * @since 100.1.0
     */
    public function setAggregator($aggregator);

    /**
     * @return string
     * @since 100.1.0
     */
    public function getAggregator();

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface[] $conditions
     * @return $this
     * @since 100.1.0
     */
    public function setConditions($conditions);

    /**
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface[]|null
     * @since 100.1.0
     */
    public function getConditions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogRule\Api\Data\ConditionExtensionInterface|null
     * @since 100.1.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.0
     */
    public function setExtensionAttributes(
        \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    );
}
