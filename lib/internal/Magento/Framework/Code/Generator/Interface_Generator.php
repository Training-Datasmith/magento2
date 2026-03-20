<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

/**
 * Interface generator.
 */
class Interface_Generator extends \Magento\Framework\Code\Generator\Class_Generator
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if (!$this->is_source_dirty()) {
            $output = $this->get_source_content();
            if (!empty($output)) {
                return $output;
            }
        }
        $output = '';
        if (!$this->get_name()) {
            return $output;
        }
        $output .= $this->generate_directives();
        if (null !== $doc_block = $this->get_doc_block()) {
            $doc_block->set_indentation('');
            $output .= $doc_block->generate();
        }
        $output .= 'interface ' . $this->get_name();
        if (!empty($this->extended_class)) {
            $output .= ' extends \\' . ltrim($this->extended_class, '\\');
        }
        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED . $this->generate_methods() . self::LINE_FEED . '}' . self::LINE_FEED;
        return $output;
    }
    /**
     * Instantiate interface method generator object.
     *
     * @return \Magento\Framework\Code\Generator\InterfaceMethodGenerator
     */
    protected function create_method_generator()
    {
        return new \Magento\Framework\Code\Generator\Interface_Method_Generator();
    }
    /**
     * Generate methods.
     *
     * @return string
     */
    protected function generate_methods()
    {
        $output = '';
        $methods = $this->get_methods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $output .= $method->generate() . self::LINE_FEED;
            }
        }
        return $output;
    }
    /**
     * Generate directives.
     *
     * @return string
     */
    protected function generate_directives()
    {
        $output = '';
        $namespace = $this->get_namespace_name();
        if (null !== $namespace) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }
        $uses = $this->get_uses();
        if (!empty($uses)) {
            foreach ($uses as $use) {
                $output .= 'use ' . $use . ';' . self::LINE_FEED;
            }
            $output .= self::LINE_FEED;
        }
        return $output;
    }
}