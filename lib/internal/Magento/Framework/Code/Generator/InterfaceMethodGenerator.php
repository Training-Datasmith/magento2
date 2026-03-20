<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

/**
 * Interface method code generator
 */
class Interface_Method_Generator extends \Laminas\Code\Generator\Method_Generator
{
    /**
     * @inheritDoc
     */
    public function generate()
    {
        $this->validate_method_modifiers();
        $output = '';
        if (!$this->get_name()) {
            return $output;
        }
        $indent = $this->get_indentation();
        if (($doc_block = $this->get_doc_block()) !== null) {
            $doc_block->set_indentation($indent);
            $output .= $doc_block->generate();
        }
        $output .= $indent;
        $output .= $this->get_visibility() . ($this->is_static() ? ' static' : '') . ' function ' . $this->get_name() . '(';
        $parameters = $this->get_parameters();
        if (!empty($parameters)) {
            $parameter_output = [];
            foreach ($parameters as $parameter) {
                $parameter_output[] = $parameter->generate();
            }
            $output .= implode(', ', $parameter_output);
        }
        $output .= ');' . self::LINE_FEED;
        return $output;
    }
    /**
     * Ensure that used method modifiers are allowed for interface methods.
     *
     * @throws \LogicException
     * @return void
     */
    protected function validate_method_modifiers()
    {
        if ($this->get_visibility() != self::VISIBILITY_PUBLIC) {
            throw new \LogicException("Interface method visibility can only be 'public'. Method name: '{$this->get_name()}'");
        }
        if ($this->is_final()) {
            throw new \LogicException("Interface method cannot be marked as 'final'. Method name: '{$this->get_name()}'");
        }
        if ($this->is_abstract()) {
            throw new \LogicException("'abstract' modifier cannot be used for interface method. Method name: '{$this->get_name()}'");
        }
    }
}