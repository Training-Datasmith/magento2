<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
use Magento\Framework\Controller\Abstract_Result;
/**
 * A result that contains raw response - may be good for passing through files,
 * returning result of downloads or some other binary contents
 *
 * @api
 */
class Raw extends Abstract_Result
{
    /**
     * @var string
     */
    protected $contents;
    /**
     * @param string $contents
     * @return $this
     */
    public function set_contents($contents)
    {
        $this->contents = $contents;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function render(Http_Response_Interface $response)
    {
        $response->set_body($this->contents);
        return $this;
    }
}