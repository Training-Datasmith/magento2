<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\TemplateEngine\Xhtml\Compiler\Element;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class Form
 */
class Form implements ElementInterface
{
    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     */
    public function compile(
        CompilerInterface $compiler,
        \DOMElement $node,
        DataObject $processedObject,
        DataObject $context
    ) {
        foreach ($this->getChildNodes($node) as $child) {
            $compiler->compile($child, $processedObject, $context);
        }
    }

    /**
     * Get child nodes
     *
     * @param \DOMElement $node
     * @return \DOMElement[]
     */
    protected function getChildNodes(\DOMElement $node)
    {
        $childNodes = [];
        foreach ($node->childNodes as $child) {
            $childNodes[] = $child;
        }

        return $childNodes;
    }
}
