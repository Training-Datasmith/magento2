<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Convert;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Convert the array data to SimpleXMLElement object
 */
class Convert_Array
{
    /**
     * Transform an assoc array to \SimpleXMLElement object
     * Array has some limitations. Appropriate exceptions will be thrown
     *
     * @param array $array
     * @param string $rootName
     * @return \SimpleXMLElement
     * @throws LocalizedException
     */
    public function assoc_to_xml(array $array, $root_name = '_')
    {
        if (empty($root_name) || is_numeric($root_name)) {
            throw new Localized_Exception(new \Magento\Framework\Phrase("The root element can't be empty or use numbers. Change the element and try again."));
        }
        $xml_str = <<<XML
        <?xml version='1.0' encoding='UTF-8' standalone='yes'?>
        <{$root_name}></{$root_name}>
        XML;
        $xml = new \Simple_Xml_Element($xml_str);
        foreach (array_keys($array) as $key) {
            if (is_numeric($key)) {
                throw new Localized_Exception(new \Magento\Framework\Phrase('An error occurred. Use non-numeric array root keys and try again.'));
            }
        }
        return self::_assoc_to_xml($array, $root_name, $xml);
    }
    /**
     * Convert nested array into flat array.
     *
     * @param array $data
     * @return array
     */
    public static function to_flat_array($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::to_flat_array($value);
                unset($data[$key]);
                $data = array_merge($data, $value);
            }
        }
        return $data;
    }
    /**
     * Function, that actually recursively transforms array to xml
     *
     * @param array $array
     * @param string $rootName
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     * @throws LocalizedException
     */
    private function _assoc_to_xml(array $array, $root_name, \Simple_Xml_Element $xml)
    {
        $has_numeric_key = false;
        $has_string_key = false;
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                if (is_string($key)) {
                    if ($key === $root_name) {
                        throw new Localized_Exception(new \Magento\Framework\Phrase("An associative key can't be the same as its parent associative key. " . 'Verify and try again.'));
                    }
                    $has_string_key = true;
                    $xml->add_child($key, $value);
                } elseif (is_int($key)) {
                    $has_numeric_key = true;
                    $xml->add_child($key, $value);
                }
            } else {
                $xml->add_child($key);
                self::_assoc_to_xml($value, $key, $xml->{$key});
            }
        }
        if ($has_numeric_key && $has_string_key) {
            throw new Localized_Exception(new \Magento\Framework\Phrase("Associative and numeric keys can't be mixed at one level. Verify and try again."));
        }
        return $xml;
    }
}