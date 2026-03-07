<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Plugin;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Validator\DirectiveValidator;
use Magento\Framework\Exception\LocalizedException;

class PageRepositoryValidatePlugin
{
    /**
     * @var DirectiveValidator
     */
    private DirectiveValidator $validator;

    /**
     * @param DirectiveValidator $validator
     */
    public function __construct(DirectiveValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate Cms Page before save
     *
     * @param PageRepositoryInterface $subject
     * @param PageInterface $page
     * @return PageInterface[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(PageRepositoryInterface $subject, PageInterface $page): array
    {
        if (!$this->validator->isValid((string)$page->getContent())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('CMS page contains invalid content — please review and correct the page.')
            );
        }
        return [$page];
    }
}
