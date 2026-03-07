<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Frontend form key content block
 */

namespace Magento\Cookie\Block\Html;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     * @param CookieHelper|null $cookieHelper
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        ?CookieHelper $cookieHelper = null
    ) {
        $data['cookieHelper'] = $cookieHelper ?? ObjectManager::getInstance()->get(CookieHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Link to cookie restriction privacy policy page
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicyLink()
    {
        return $this->_urlBuilder->getUrl('privacy-policy-cookie-restriction-mode');
    }
}
