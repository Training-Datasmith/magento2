<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Layout;

/**
 * Class Element
 *
 * @api
 * @since 100.0.2
 */
class Element extends \Magento\Framework\Simplexml\Element
{
    /**#@+
     * Supported layout directives
     */
    public const TYPE_RENDERER = 'renderer';

    public const TYPE_TEMPLATE = 'template';

    public const TYPE_DATA = 'data';

    public const TYPE_BLOCK = 'block';

    public const TYPE_CONTAINER = 'container';

    public const TYPE_ACTION = 'action';

    public const TYPE_ARGUMENTS = 'arguments';

    public const TYPE_ARGUMENT = 'argument';

    public const TYPE_REFERENCE_BLOCK = 'referenceBlock';

    public const TYPE_REFERENCE_CONTAINER = 'referenceContainer';

    public const TYPE_REMOVE = 'remove';

    public const TYPE_MOVE = 'move';

    public const TYPE_UI_COMPONENT = 'uiComponent';

    public const TYPE_HEAD = 'head';

    /**#@-*/

    /**#@+
     * Names of container options in layout
     */
    public const CONTAINER_OPT_HTML_TAG = 'htmlTag';

    public const CONTAINER_OPT_HTML_CLASS = 'htmlClass';

    public const CONTAINER_OPT_HTML_ID = 'htmlId';

    public const CONTAINER_OPT_LABEL = 'label';

    /**#@-*/

    /**
     * Prepare the element
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepare()
    {
        switch ($this->getName()) {
            case self::TYPE_BLOCK:
            case self::TYPE_RENDERER:
            case self::TYPE_TEMPLATE:
            case self::TYPE_DATA:
            case self::TYPE_UI_COMPONENT:
                $this->prepareBlock();
                break;
            case self::TYPE_REFERENCE_BLOCK:
            case self::TYPE_REFERENCE_CONTAINER:
                $this->prepareReference();
                break;
            case self::TYPE_ACTION:
                $this->prepareAction();
                break;
            case self::TYPE_ARGUMENT:
                $this->prepareActionArgument();
                break;
            default:
                break;
        }
        foreach ($this as $child) {
            /** @var Element $child */
            $child->prepare();
        }
        return $this;
    }

    /**
     * Get block name
     *
     * @return bool|string
     */
    public function getBlockName()
    {
        $tagName = (string)$this->getName();
        $isThisBlock = empty($this['name']) || !in_array(
            $tagName,
            [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK]
        );

        if ($isThisBlock) {
            return false;
        }
        return (string)$this['name'];
    }

    /**
     * Get element name
     *
     * Advanced version of getBlockName() method: gets name for container as well as for block
     *
     * @return string|bool
     */
    public function getElementName()
    {
        $tagName = $this->getName();
        $isThisContainer = !in_array(
            $tagName,
            [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK, self::TYPE_CONTAINER, self::TYPE_REFERENCE_CONTAINER]
        );

        if ($isThisContainer) {
            return false;
        }
        return $this->getAttribute('name');
    }

    /**
     * Extracts sibling from 'before' and 'after' attributes
     *
     * @return string
     */
    public function getSibling()
    {
        $sibling = null;
        if ($this->getAttribute('before')) {
            $sibling = $this->getAttribute('before');
        } elseif ($this->getAttribute('after')) {
            $sibling = $this->getAttribute('after');
        }

        return $sibling;
    }

    /**
     * Add parent element name to parent attribute
     *
     * @return $this
     */
    public function prepareBlock()
    {
        $parent = $this->getParent();
        if (isset($parent['name']) && !isset($this['parent'])) {
            $this->addAttribute('parent', (string)$parent['name']);
        }

        return $this;
    }

    /**
     * Prepare references
     *
     * @return $this
     */
    public function prepareReference()
    {
        return $this;
    }

    /**
     * Add parent element name to block attribute
     *
     * @return $this
     */
    public function prepareAction()
    {
        $parent = $this->getParent();
        $this->addAttribute('block', (string)$parent['name']);

        return $this;
    }

    /**
     * Prepare action argument
     *
     * @return $this
     */
    public function prepareActionArgument()
    {
        return $this;
    }

    /**
     * Returns information is this element allows caching
     *
     * @return bool
     */
    public function isCacheable()
    {
        return !(bool)count($this->xpath('//' . self::TYPE_BLOCK . '[@cacheable="false"]'));
    }
}
