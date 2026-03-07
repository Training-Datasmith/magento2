<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Block;

use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\View\Element\Template;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;

abstract class SearchData extends Template implements SearchDataInterface
{
    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var string
     */
    protected $_template = 'Magento_AdvancedSearch::search_data.phtml';

    /**
     * @param string $title
     */
    public function __construct(
        Template\Context $context,
        private readonly SuggestedQueriesInterface $searchDataProvider,
        QueryFactoryInterface $queryFactory,
        protected $title,
        array $data = []
    ) {
        $this->query = $queryFactory->get();
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->searchDataProvider->getItems($this->query);
    }

    /**
     * @inheritdoc
     */
    public function isShowResultsCount()
    {
        return $this->searchDataProvider->isResultsCountEnabled();
    }

    /**
     * @inheritdoc
     */
    public function getLink($queryText)
    {
        return $this->getUrl('*/*/') . '?q=' . urlencode($queryText);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return __($this->title);
    }
}
