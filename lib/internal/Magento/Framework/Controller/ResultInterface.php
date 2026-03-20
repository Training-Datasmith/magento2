<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller;

use Magento\Framework\App\Response_Interface;
/**
 * An abstraction of result that controller actions must return
 * The point of this kind of object is to encapsulate all information/objects relevant to the result
 * and be able to set it to the HTTP response
 *
 * @api
 * @since 100.0.2
 */
interface Result_Interface
{
    /**
     * @param int $httpCode
     * @return $this
     */
    public function set_http_response_code($http_code);
    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return $this
     */
    public function set_header($name, $value, $replace = false);
    /**
     * Render result and set to response
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function render_result(Response_Interface $response);
}