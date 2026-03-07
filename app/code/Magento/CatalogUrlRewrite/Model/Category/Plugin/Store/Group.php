<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Scheduler;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group as StoreGroup;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Generate Product and Category URLs
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Group
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Scheduler
     */
    private $scheduler;
    /**
     * @param UrlPersistInterface $urlPersist
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param StoreManagerInterface $storeManager
     * @param Scheduler $scheduler
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        StoreManagerInterface $storeManager,
        Scheduler $scheduler
    ) {
        $this->urlPersist = $urlPersist;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->storeManager = $storeManager;
        $this->scheduler = $scheduler;
    }

    /**
     * Perform updating url for categories and products assigned to the group
     *
     * @param StoreGroup $subject
     * @param StoreGroup $result
     * @param AbstractModel $group
     * @return StoreGroup
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        StoreGroup $subject,
        StoreGroup $result,
        AbstractModel $group
    ) {
        if (!$group->isObjectNew()
            && $group->getStoreIds()
            && ($group->dataHasChangedFor('website_id')
                || $group->dataHasChangedFor('root_category_id'))
        ) {
            $this->storeManager->reinitStores();
            foreach ($group->getStoreIds() as $storeId) {
                $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $storeId]);
            }

            $this->urlPersist->replace(
                $this->generateCategoryUrls($group->getRootCategoryId(), $group->getStoreIds())
            );

            $websiteId = $group->getWebsiteId();
            $originWebsiteId = $group->getOrigData('website_id');

            if ($originWebsiteId !== null && $websiteId !== $originWebsiteId) {
                $websiteIds = [$websiteId, $originWebsiteId];
            } else {
                $websiteIds = [$websiteId];
            }
            foreach ($websiteIds as $websiteId) {
                $this->scheduler->execute($websiteId);
            }
        }

        return $result;
    }

    /**
     * Generate url rewrites for categories assigned to store
     *
     * @param int $rootCategoryId
     * @param array $storeIds
     * @return array
     */
    protected function generateCategoryUrls($rootCategoryId, $storeIds)
    {
        $urls = [];
        $categories = $this->categoryFactory->create()->getCategories($rootCategoryId, 1, false, true);
        foreach ($categories as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category->setStoreId(Store::DEFAULT_STORE_ID);
            $category->setStoreIds($storeIds);
            $urls[] = $this->categoryUrlRewriteGenerator->generate($category);
        }

        return array_merge([], ...$urls);
    }
}
