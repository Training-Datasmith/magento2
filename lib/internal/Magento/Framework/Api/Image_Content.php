<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\Image_Content_Interface;
/**
 * Image Content data object
 *
 * @codeCoverageIgnore
 */
class Image_Content extends Abstract_Simple_Object implements Image_Content_Interface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_base64encoded_data()
    {
        return $this->_get(self::BASE64_ENCODED_DATA);
    }
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_type()
    {
        return $this->_get(self::TYPE);
    }
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_name()
    {
        return $this->_get(self::NAME);
    }
    /**
     * {@inheritdoc}
     *
     * @param string $data
     * @return $this
     */
    public function set_base64encoded_data($data)
    {
        return $this->set_data(self::BASE64_ENCODED_DATA, $data);
    }
    /**
     * {@inheritdoc}
     *
     * @param string $mimeType
     * @return $this
     */
    public function set_type($mime_type)
    {
        return $this->set_data(self::TYPE, $mime_type);
    }
    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return $this
     */
    public function set_name($name)
    {
        return $this->set_data(self::NAME, $name);
    }
}