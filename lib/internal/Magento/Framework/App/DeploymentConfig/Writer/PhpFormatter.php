<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Deployment_Config\Writer;

/**
 * A formatter for deployment configuration that presents it as a PHP-file that returns data
 */
class Php_Formatter implements Formatter_Interface
{
    /**
     * 4 space indentation for array formatting.
     */
    public const INDENT = '    ';
    /**
     * Format deployment configuration.
     *
     * If $comments is present, each item will be added
     * as comment to the corresponding section
     *
     * @inheritdoc
     */
    public function format($data, array $comments = [])
    {
        if (!empty($comments) && is_array($data)) {
            return "<?php\nreturn [\n" . $this->format_data($data, $comments) . "\n];\n";
        }
        return "<?php\nreturn " . $this->var_export_short($data, true) . ";\n";
    }
    /**
     * Format supplied data
     *
     * @param string[] $data
     * @param string[] $comments
     * @param string $prefix
     * @return string
     */
    private function format_data($data, $comments = [], $prefix = '    ')
    {
        $elements = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!empty($comments[$key])) {
                    $elements[] = $prefix . '/**';
                    $elements[] = $prefix . ' * For the section: ' . $key;
                    foreach (explode("\n", $comments[$key]) as $comment_line) {
                        $elements[] = $prefix . ' * ' . $comment_line;
                    }
                    $elements[] = $prefix . ' */';
                }
                if (is_array($value)) {
                    $elements[] = $prefix . $this->var_export_short($key) . ' => [';
                    $elements[] = $this->format_data($value, [], '    ' . $prefix);
                    $elements[] = $prefix . '],';
                } else {
                    $elements[] = $prefix . $this->var_export_short($key) . ' => ' . $this->var_export_short($value) . ',';
                }
            }
            return implode("\n", $elements);
        }
        return var_export($data, true);
    }
    /**
     * Format generated config files using the short array syntax.
     *
     * If variable to export is an array, format with the php >= 5.4 short array syntax. Otherwise use
     * default var_export functionality.
     *
     * @param mixed $var
     * @param integer $depth
     * @return string
     */
    private function var_export_short($var, int $depth = 0)
    {
        if (null === $var) {
            return 'null';
        } elseif (!is_array($var)) {
            return var_export($var, true);
        }
        $indexed = array_keys($var) === range(0, count($var) - 1);
        $expanded = [];
        foreach ($var as $key => $value) {
            $expanded[] = str_repeat(self::INDENT, $depth) . ($indexed ? '' : $this->var_export_short($key) . ' => ') . $this->var_export_short($value, $depth + 1);
        }
        return sprintf("[\n%s\n%s]", implode(",\n", $expanded), str_repeat(self::INDENT, $depth - 1));
    }
}