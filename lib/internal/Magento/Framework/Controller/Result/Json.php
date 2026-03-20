<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
use Magento\Framework\Controller\Abstract_Result;
use Magento\Framework\Translate\Inline_Interface;
/**
 * A possible implementation of JSON response type (instead of hardcoding json_encode() all over the place)
 * Actual for controller actions that serve ajax requests
 *
 * @api
 * @since 100.0.2
 */
class Json extends Abstract_Result
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translate_inline;
    /**
     * @var string
     */
    protected $json;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(Inline_Interface $translate_inline, ?\Magento\Framework\Serialize\Serializer\Json $serializer = null)
    {
        $this->translate_inline = $translate_inline;
        $this->serializer = $serializer ?: \Magento\Framework\App\Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }
    /**
     * Set json data
     *
     * @param mixed $data
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set_data($data, $cycle_check = false, $options = [])
    {
        if ($data instanceof \Magento\Framework\Data_Object) {
            $data = $data->to_array();
        }
        $this->json = $this->serializer->serialize($data);
        return $this;
    }
    /**
     * @param string $jsonData
     * @return $this
     */
    public function set_json_data($json_data)
    {
        $this->json = (string) $json_data;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function render(Http_Response_Interface $response)
    {
        $this->translate_inline->process_response_body($this->json, true);
        $response->set_header('Content-Type', 'application/json', true);
        $response->set_body($this->json);
        return $this;
    }
}