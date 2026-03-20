<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url\Helper\Data as UrlHelper;
/**
 * Helper to obtain post data for postData widget
 */
class Post_Helper extends \Magento\Framework\App\Helper\Abstract_Helper
{
    /**
     * @var UrlHelper
     */
    private $url_helper;
    /**
     * @param Context $context
     * @param UrlHelper $urlHelper
     */
    public function __construct(Context $context, Url_Helper $url_helper)
    {
        parent::__construct($context);
        $this->url_helper = $url_helper;
    }
    /**
     * Get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     *
     * @return string
     */
    public function get_post_data($url, array $data = [])
    {
        if (!isset($data[\Magento\Framework\App\Action_Interface::PARAM_NAME_URL_ENCODED])) {
            $data[\Magento\Framework\App\Action_Interface::PARAM_NAME_URL_ENCODED] = $this->url_helper->get_encoded_url();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }
}