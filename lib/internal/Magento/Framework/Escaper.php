<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework;

use Exception;
/**
 * Magento escape methods
 *
 * @api
 * @since 100.0.2
 */
class Escaper
{
    /**
     * HTML special characters flag
     * @var int
     */
    private $html_special_chars_flag = ENT_QUOTES | ENT_SUBSTITUTE;
    /**
     * @var \Magento\Framework\ZendEscaper
     */
    private $escaper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    private $translate_inline;
    /**
     * @var string[]
     */
    private $not_allowed_tags = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];
    /**
     * @var string[]
     */
    private $allowed_attributes = ['id', 'class', 'href', 'title', 'style'];
    /**
     * @var array
     */
    private $not_allowed_attributes = ['a' => ['style']];
    /**
     * @var string
     */
    private static $xss_filtration_pattern = '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:))|' . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|' . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';
    /**
     * @var string[]
     */
    private $escape_as_url_attributes = ['href'];
    /**
     * Escape string for HTML context.
     *
     * AllowedTags will not be escaped, except the following: script, img, embed,
     * iframe, video, source, object, audio
     *
     * @param string|int|float|\Stringable|array<string|int|float|\Stringable> $data
     * @param array|null $allowedTags
     * @return ($data is array ? string[] : string)
     */
    public function escape_html($data, $allowed_tags = null)
    {
        if (!is_array($data)) {
            $data = (string) $data;
        }
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escape_html($item, $allowed_tags);
            }
        } elseif (!empty($data)) {
            if (is_array($allowed_tags) && !empty($allowed_tags)) {
                $allowed_tags = $this->filter_prohibited_tags($allowed_tags);
                $wrapper_element_id = uniqid();
                $dom_document = new \Dom_Document('1.0', 'UTF-8');
                set_error_handler(function ($error_number, $error_string) {
                    // phpcs:ignore Magento2.Exceptions.DirectThrow
                    throw new \InvalidArgumentException($error_string, $error_number);
                });
                $data = $this->prepare_unescaped_characters($data);
                $convmap = [0x80, 0x10ffff, 0, 0x1fffff];
                $string = mb_encode_numericentity($data, $convmap, 'UTF-8');
                try {
                    $dom_document->load_html('<html><body id="' . $wrapper_element_id . '">' . $string . '</body></html>');
                } catch (Exception $e) {
                    $this->get_logger()->critical($e);
                } finally {
                    restore_error_handler();
                }
                $this->remove_comments($dom_document);
                $this->remove_not_allowed_tags($dom_document, $allowed_tags);
                $this->remove_not_allowed_attributes($dom_document);
                $this->escape_text($dom_document);
                $this->escape_attribute_values($dom_document);
                $result = mb_decode_numericentity(
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    html_entity_decode($dom_document->save_html(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    $convmap,
                    'UTF-8'
                );
                preg_match('/<body id="' . $wrapper_element_id . '">(.+)<\/body><\/html>$/si', $result, $matches);
                return !empty($matches) ? $matches[1] : '';
            } else {
                $result = htmlspecialchars($data, $this->html_special_chars_flag, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }
    /**
     * Used to replace characters, that mb_convert_encoding will not process
     *
     * @param string $data
     * @return string|null
     */
    private function prepare_unescaped_characters(string $data): ?string
    {
        $patterns = ['/\&/u'];
        $replacements = ['&amp;'];
        return \preg_replace($patterns, $replacements, $data);
    }
    /**
     * Remove not allowed tags
     *
     * @param \DOMDocument $domDocument
     * @param string[] $allowedTags
     * @return void
     */
    private function remove_not_allowed_tags(\Dom_Document $dom_document, array $allowed_tags)
    {
        $xpath = new \Domx_Path($dom_document);
        $nodes = $xpath->query('//node()[name() != \'' . implode('\' and name() != \'', array_merge($allowed_tags, ['html', 'body'])) . '\']');
        foreach ($nodes as $node) {
            if ($node->node_name != '#text') {
                $node->parent_node->replace_child($dom_document->create_text_node($node->text_content), $node);
            }
        }
    }
    /**
     * Remove not allowed attributes
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function remove_not_allowed_attributes(\Dom_Document $dom_document)
    {
        $xpath = new \Domx_Path($dom_document);
        $nodes = $xpath->query('//@*[name() != \'' . implode('\' and name() != \'', $this->allowed_attributes) . '\']');
        foreach ($nodes as $node) {
            $node->parent_node->remove_attribute($node->node_name);
        }
        foreach ($this->not_allowed_attributes as $tag => $attributes) {
            $nodes = $xpath->query('//@*[name() =\'' . implode('\' or name() = \'', $attributes) . '\']' . '[parent::node()[name() = \'' . $tag . '\']]');
            foreach ($nodes as $node) {
                $node->parent_node->remove_attribute($node->node_name);
            }
        }
    }
    /**
     * Remove comments
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function remove_comments(\Dom_Document $dom_document)
    {
        $xpath = new \Domx_Path($dom_document);
        $nodes = $xpath->query('//comment()');
        foreach ($nodes as $node) {
            $node->parent_node->remove_child($node);
        }
    }
    /**
     * Escape text
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escape_text(\Dom_Document $dom_document)
    {
        $xpath = new \Domx_Path($dom_document);
        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->text_content = $this->escape_html($node->text_content);
        }
    }
    /**
     * Escape attribute values
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escape_attribute_values(\Dom_Document $dom_document)
    {
        $xpath = new \Domx_Path($dom_document);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $this->escape_attribute_value($node->node_name, $node->parent_node->get_attribute($node->node_name));
            $node->parent_node->set_attribute($node->node_name, $value);
        }
    }
    /**
     * Escape attribute value using escapeHtml or escapeUrl
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function escape_attribute_value($name, $value)
    {
        return in_array($name, $this->escape_as_url_attributes) ? $this->escape_url($value) : $this->escape_html($value);
    }
    /**
     * Escape a string for the HTML attribute context
     *
     * @param string|int|float|\Stringable $string
     * @param boolean $escapeSingleQuote
     * @return string
     * @since 101.0.0
     */
    public function escape_html_attr($string, $escape_single_quote = true)
    {
        $string = (string) $string;
        if ($escape_single_quote) {
            $translate_inline = $this->get_translate_inline();
            return $translate_inline->is_allowed() ? $this->inline_sensitive_escape_html_attr($string) : $this->get_escaper()->escape_html_attr($string);
        }
        return htmlspecialchars($string, $this->html_special_chars_flag, 'UTF-8', false);
    }
    /**
     * Escape URL
     *
     * @param string $string
     * @return string
     */
    public function escape_url($string)
    {
        return $this->escape_html($this->escape_xss_in_url($string));
    }
    /**
     * Encode URL
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     */
    public function encode_url_param($string)
    {
        return $this->get_escaper()->escape_url((string) $string);
    }
    /**
     * Escape string for the JavaScript context
     *
     * @param string|int|float|\Stringable $string
     * @return string
     * @since 101.0.0
     */
    public function escape_js($string)
    {
        if (!is_string($string)) {
            // In PHP > 8, preg_replace_callback throws an error if the 3rd param type is incorrect.
            // This check emulates an old behavior.
            $string = (string) $string;
        }
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }
        return preg_replace_callback('/[^a-z0-9,\._]/iSu', function ($matches) {
            $chr = $matches[0];
            if (strlen($chr) != 1) {
                $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                $chr = $chr === false ? '' : $chr;
            }
            return sprintf('\u%04s', strtoupper(bin2hex($chr)));
        }, $string);
    }
    /**
     * Escape string for the CSS context
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     */
    public function escape_css($string)
    {
        return $this->get_escaper()->escape_css((string) $string);
    }
    /**
     * Escape single quotes/apostrophes ('), or other specified $quote character in javascript
     *
     * @param string|string[]|array $data
     * @param string $quote
     * @return string|array
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    public function escape_js_quote($data, $quote = '\'')
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escape_js_quote($item, $quote);
            }
        } else {
            $result = str_replace($quote, '\\' . $quote, (string) $data);
        }
        return $result;
    }
    /**
     * Escape xss in urls
     *
     * @param string $data
     * @return string
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    public function escape_xss_in_url($data)
    {
        $data = html_entity_decode((string) $data);
        $this->get_translate_inline()->process_response_body($data);
        return htmlspecialchars($this->escape_script_identifiers($data), $this->html_special_chars_flag | ENT_HTML5 | ENT_HTML401, 'UTF-8', false);
    }
    /**
     * Remove `javascript:`, `vbscript:`, `data:` words from the string.
     *
     * @param string $data
     * @return string
     */
    private function escape_script_identifiers(string $data): string
    {
        $filtered_data = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $data);
        if ($filtered_data === null || $filtered_data === '') {
            return '';
        }
        $filtered_data = preg_replace(self::$xss_filtration_pattern, ':', $filtered_data);
        if ($filtered_data === null) {
            return '';
        }
        if (preg_match(self::$xss_filtration_pattern, $filtered_data)) {
            $filtered_data = $this->escape_script_identifiers($filtered_data);
        }
        return $filtered_data;
    }
    /**
     * Escape quotes inside html attributes
     *
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    public function escape_quote($data, $add_slashes = false)
    {
        if ($add_slashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, $this->html_special_chars_flag, null, false);
    }
    /**
     * Get escaper
     *
     * @return \Magento\Framework\ZendEscaper
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    private function get_escaper()
    {
        if ($this->escaper == null) {
            $this->escaper = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Zend_Escaper::class);
        }
        return $this->escaper;
    }
    /**
     * Get logger
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    private function get_logger()
    {
        if ($this->logger == null) {
            $this->logger = \Magento\Framework\App\Object_Manager::get_instance()->get(\Psr\Log\Logger_Interface::class);
        }
        return $this->logger;
    }
    /**
     * Filter prohibited tags.
     *
     * @param string[] $allowedTags
     * @return string[]
     */
    private function filter_prohibited_tags(array $allowed_tags): array
    {
        $not_allowed_tags = array_intersect(array_map('strtolower', $allowed_tags), $this->not_allowed_tags);
        if (!empty($not_allowed_tags)) {
            $this->get_logger()->critical('The following tag(s) are not allowed: ' . implode(', ', $not_allowed_tags));
            $allowed_tags = array_diff($allowed_tags, $this->not_allowed_tags);
        }
        return $allowed_tags;
    }
    /**
     * Resolve inline translator.
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    private function get_translate_inline()
    {
        if ($this->translate_inline === null) {
            $this->translate_inline = \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Translate\Inline_Interface::class);
        }
        return $this->translate_inline;
    }
    /**
     * Inline sensitive escape attribute value.
     *
     * @param string $text
     * @return string
     */
    private function inline_sensitive_escape_html_attr(string $text): string
    {
        $escaper = $this->get_escaper();
        $text_length = strlen($text);
        if ($text_length < 6) {
            return $escaper->escape_html_attr($text);
        }
        $first_characters = substr($text, 0, 3);
        $last_characters = substr($text, -3, 3);
        if ($first_characters !== '{{{' || $last_characters !== '}}}') {
            return $escaper->escape_html_attr($text);
        }
        $text = substr($text, 3, $text_length - 6);
        $strings = explode('}}{{', $text);
        $escaped_strings = [];
        foreach ($strings as $string) {
            $escaped_strings[] = $escaper->escape_html_attr($string);
        }
        return '{{{' . implode('}}{{', $escaped_strings) . '}}}';
    }
}