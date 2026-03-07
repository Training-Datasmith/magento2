<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\AdvancedPricingImportExport\Model\CurrencyResolver;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 *  Import advanced pricing class
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AdvancedPricing extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    public const VALUE_ALL_GROUPS = 'ALL GROUPS';
    public const VALUE_ALL_WEBSITES = 'All Websites';
    public const COL_SKU = 'sku';
    public const COL_TIER_PRICE_WEBSITE = 'tier_price_website';
    public const COL_TIER_PRICE_CUSTOMER_GROUP = 'tier_price_customer_group';
    public const COL_TIER_PRICE_QTY = 'tier_price_qty';
    public const COL_TIER_PRICE = 'tier_price';
    public const COL_TIER_PRICE_PERCENTAGE_VALUE = 'percentage_value';
    public const COL_TIER_PRICE_TYPE = 'tier_price_value_type';
    public const TIER_PRICE_TYPE_FIXED = 'Fixed';
    public const TIER_PRICE_TYPE_PERCENT = 'Discount';
    public const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';
    public const DEFAULT_ALL_GROUPS_GROUPED_PRICE_VALUE = '0';
    public const ENTITY_TYPE_CODE = 'advanced_pricing';
    public const VALIDATOR_MAIN = 'validator';
    public const VALIDATOR_WEBSITE = 'validator_website';
    private const VALIDATOR_TIER_PRICE = 'validator_tier_price';

    private const ERROR_DUPLICATE_TIER_PRICE = 'duplicateTierPrice';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        ValidatorInterface::ERROR_SKU_IS_EMPTY => 'SKU is empty',
        ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'Product with specified SKU not found',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_QTY => 'Tier Price data price or quantity value is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_SITE => 'Tier Price data website is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_GROUP => 'Tier Price customer group is invalid',
        ValidatorInterface::ERROR_INVALID_TIER_PRICE_TYPE => 'Value for \'tier_price_value_type\' ' .
            'attribute contains incorrect value, acceptable values are Fixed, Discount',
        ValidatorInterface::ERROR_TIER_DATA_INCOMPLETE => 'Tier Price data is incomplete',
        ValidatorInterface::ERROR_INVALID_ATTRIBUTE_DECIMAL => 'Value for \'%s\' attribute contains incorrect value,' .
            ' acceptable values are in decimal format',
        self::ERROR_DUPLICATE_TIER_PRICE => 'We found a duplicate website, tier price, customer group' .
            ' and quantity.',
    ];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::COL_SKU,
        self::COL_TIER_PRICE_WEBSITE,
        self::COL_TIER_PRICE_CUSTOMER_GROUP,
        self::COL_TIER_PRICE_QTY,
        self::COL_TIER_PRICE,
        self::COL_TIER_PRICE_TYPE,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * @var array
     */
    protected $_validators = [];

    /**
     * @var array
     */
    protected $_cachedSkuToDelete;

    /**
     * @var array
     */
    protected $_oldSkus;

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /**
     * @var string
     */
    protected $_catalogProductEntity;

    /**
     * @var string
     */
    private $productEntityLinkField;

    private array $websiteScopeTierPrice = [];

    private array $globalScopeTierPrice = [];

    private array $allProductIds = [];

    /**
     * @var CurrencyResolver
     */
    private $currencyResolver;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        protected \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        protected \Magento\Catalog\Model\Product $_productModel,
        protected \Magento\Catalog\Helper\Data $_catalogData,
        protected \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $_storeResolver,
        protected \Magento\CatalogImportExport\Model\Import\Product $_importProduct,
        AdvancedPricing\Validator $validator,
        AdvancedPricing\Validator\Website $websiteValidator,
        AdvancedPricing\Validator\TierPrice $tierPriceValidator,
        ?CurrencyResolver $currencyResolver = null
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
        $this->_resourceFactory = $resourceFactory;
        $this->_validators[self::VALIDATOR_MAIN] = $validator->init($this);
        $this->_catalogProductEntity = $this->_resourceFactory->create()->getTable('catalog_product_entity');
        $this->_oldSkus = $this->retrieveOldSkus();
        $this->_validators[self::VALIDATOR_WEBSITE] = $websiteValidator;
        $this->_validators[self::VALIDATOR_TIER_PRICE] = $tierPriceValidator;
        $this->errorAggregator = $errorAggregator;
        $this->currencyResolver = $currencyResolver ?? ObjectManager::getInstance()->get(CurrencyResolver::class);

        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
    }

    /**
     * Validator object getter.
     *
     * @param string $type
     * @return AdvancedPricing\Validator|AdvancedPricing\Validator\Website
     */
    protected function _getValidator($type)
    {
        return $this->_validators[$type];
    }

    /**
     * Entity type code getter.
     */
    public function getEntityTypeCode(): string
    {
        return 'advanced_pricing';
    }

    /**
     * Row validation.
     *
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $sku = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COL_SKU])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                return false;
            }
            return true;
        }
        if (!$this->_getValidator(self::VALIDATOR_MAIN)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }
        if (isset($rowData[self::COL_SKU])) {
            $sku = $rowData[self::COL_SKU];
        }
        if (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        }

        if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
            $this->validateRowForDuplicate($rowData, $rowNum);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData(): bool
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceAdvancedPricing();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveAdvancedPricing();
        }
        return true;
    }

    /**
     * Save advanced pricing
     *
     * @return $this
     * @throws \Exception
     */
    public function saveAdvancedPricing(): static
    {
        $this->saveAndReplaceAdvancedPrices();
        return $this;
    }

    /**
     * Deletes Advanced price data from raw data.
     *
     * @return $this
     * @throws \Exception
     */
    public function deleteAdvancedPricing(): static
    {
        $this->_cachedSkuToDelete = null;
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowSku = $rowData[self::COL_SKU];
                    $listSku[] = $rowSku;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listSku) {
            $this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE);
            $this->setUpdatedAt($listSku);
        }
        return $this;
    }

    /**
     * Replace advanced pricing
     *
     * @return $this
     * @throws \Exception
     */
    public function replaceAdvancedPricing(): static
    {
        $this->saveAndReplaceAdvancedPrices();
        return $this;
    }

    /**
     * Save and replace advanced prices
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function saveAndReplaceAdvancedPrices(): static
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $listSku = [];
        $tierPrices = [];
        while ($bunch = $this->_dataSourceModel->getNextUniqueBunch($this->getIds())) {
            $bunchTierPrices = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $tierPrice = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_FIXED
                            ? $rowData[self::COL_TIER_PRICE] : 0,
                        'percentage_value' => $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_PERCENT
                            ? $rowData[self::COL_TIER_PRICE] : null,
                        'website_id' => $this->getWebSiteId($rowData[self::COL_TIER_PRICE_WEBSITE]),
                    ];
                    if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                        $bunchTierPrices[$rowSku][] = $tierPrice;
                    }
                    if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                        $tierPrices[$rowSku][] = $tierPrice;
                    }
                }
            }

            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->processCountExistingPrices($bunchTierPrices, self::TABLE_TIER_PRICE)
                    ->processCountNewPrices($bunchTierPrices);

                $this->saveProductPrices($bunchTierPrices, self::TABLE_TIER_PRICE);
            }
        }

        if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
            if ($listSku) {
                $this->setUpdatedAt($listSku);
            }
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            if ($listSku) {
                $this->processCountNewPrices($tierPrices);
                if ($this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE)) {
                    $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                    $this->setUpdatedAt($listSku);
                }
            }
        }
        $this->finalizeCount();

        return $this;
    }

    /**
     * Save product prices.
     *
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    protected function saveProductPrices(array $priceData, $table): static
    {
        if ($priceData) {
            $tableName = $this->_resourceFactory->create()->getTable($table);
            $priceIn = [];
            $entityIds = [];
            $oldSkus = $this->retrieveOldSkus();
            foreach ($priceData as $sku => $priceRows) {
                if (isset($oldSkus[$sku])) {
                    $productId = $oldSkus[$sku];
                    foreach ($priceRows as $row) {
                        $row[$this->getProductEntityLinkField()] = $productId;
                        $priceIn[] = $row;
                        $entityIds[] = $productId;
                    }
                }
            }
            if ($priceIn) {
                $this->_connection->insertOnDuplicate($tableName, $priceIn, ['value', 'percentage_value']);
            }
        }
        return $this;
    }

    /**
     * Deletes tier prices prices.
     *
     * @param string $table
     * @throws \Exception
     */
    protected function deleteProductTierPrices(array $listSku, $table): bool
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $productEntityLinkField = $this->getProductEntityLinkField();
        if ($tableName && $listSku) {
            if (!$this->_cachedSkuToDelete) {
                $this->_cachedSkuToDelete = $this->_connection->fetchCol(
                    $this->_connection->select()
                        ->from($this->_catalogProductEntity, $productEntityLinkField)
                        ->where('sku IN (?)', $listSku)
                );
            }
            if ($this->_cachedSkuToDelete) {
                try {
                    $this->countItemsDeleted += $this->_connection->delete(
                        $tableName,
                        $this->_connection->quoteInto($productEntityLinkField . ' IN (?)', $this->_cachedSkuToDelete)
                    );
                    return true;
                } catch (\Exception) {
                    return false;
                }
            } else {
                $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, 0);
                return false;
            }
        }
        return false;
    }

    /**
     * Set updated_at for product
     *
     * @return $this
     */
    protected function setUpdatedAt(array $listSku): static
    {
        $updatedAt = $this->dateTime->gmtDate('Y-m-d H:i:s');
        $this->_connection->update(
            $this->_catalogProductEntity,
            [\Magento\Catalog\Model\Category::KEY_UPDATED_AT => $updatedAt],
            $this->_connection->quoteInto('sku IN (?)', array_unique($listSku))
        );
        return $this;
    }

    /**
     * Get website id by code
     *
     * @param string $websiteCode
     * @return array|int|string
     */
    protected function getWebSiteId($websiteCode)
    {
        return $websiteCode == $this->_getValidator(self::VALIDATOR_WEBSITE)->getAllWebsitesValue() ||
        $this->_catalogData->isPriceGlobal() ? 0 : $this->_storeResolver->getWebsiteCodeToId($websiteCode);
    }

    /**
     * Get customer group id
     *
     * @param string $customerGroup
     * @return int
     */
    protected function getCustomerGroupId($customerGroup)
    {
        $customerGroups = $this->_getValidator(self::VALIDATOR_TIER_PRICE)->getCustomerGroups();
        return $customerGroup == self::VALUE_ALL_GROUPS ? 0 : $customerGroups[$customerGroup];
    }

    /**
     * Retrieve product skus
     *
     * @return array
     * @throws \Exception
     */
    protected function retrieveOldSkus()
    {
        if ($this->_oldSkus === null) {
            $this->_oldSkus = $this->_connection->fetchPairs(
                $this->_connection->select()->from(
                    $this->_catalogProductEntity,
                    ['sku', $this->getProductEntityLinkField()]
                )
            );
        }
        return $this->_oldSkus;
    }

    /**
     * Count existing prices
     *
     * @param array $prices
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    protected function processCountExistingPrices($prices, $table): static
    {
        $oldSkus = $this->retrieveOldSkus();
        $existProductIds = array_intersect_key($oldSkus, $prices);
        if (!count($existProductIds)) {
            return $this;
        }

        $tableName = $this->_resourceFactory->create()->getTable($table);
        $productEntityLinkField = $this->getProductEntityLinkField();
        $existingPrices = $this->_connection->fetchAll(
            $this->_connection->select()->from(
                $tableName,
                [$productEntityLinkField, 'all_groups', 'customer_group_id', 'qty']
            )->where(
                $productEntityLinkField . ' IN (?)',
                $existProductIds
            )
        );
        foreach ($existingPrices as $existingPrice) {
            foreach ($prices as $sku => $skuPrices) {
                if (isset($oldSkus[$sku]) && $existingPrice[$productEntityLinkField] == $oldSkus[$sku]) {
                    $this->incrementCounterUpdated($skuPrices, $existingPrice);
                }
            }
        }

        return $this;
    }

    /**
     * Increment counter of updated items
     *
     * @param array $prices
     * @return void
     */
    protected function incrementCounterUpdated($prices, array $existingPrice)
    {
        foreach ($prices as $price) {
            if ($existingPrice['all_groups'] == $price['all_groups']
                && $existingPrice['customer_group_id'] == $price['customer_group_id']
                && (int) $existingPrice['qty'] === (int) $price['qty']
            ) {
                $this->countItemsUpdated++;
                continue;
            }
        }
    }

    /**
     * Count new prices
     *
     * @return $this
     */
    protected function processCountNewPrices(array $tierPrices): static
    {
        foreach ($tierPrices as $productPrices) {
            $this->countItemsCreated += count($productPrices);
        }

        return $this;
    }

    /**
     *  Finalize count of new and existing records
     */
    protected function finalizeCount()
    {
        $this->countItemsCreated -= $this->countItemsUpdated;
    }

    /**
     * Get product entity link field
     *
     * @return string
     * @throws \Exception
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * @inheritdoc
     */
    protected function _saveValidatedBunches()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND === $this->getBehavior()
            && !$this->_catalogData->isPriceGlobal()
        ) {
            $source = $this->_getSource();
            $source->rewind();
            while ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException) {
                    $source->next();
                    continue;
                }
                $this->validateRow($rowData, $source->key());
                $source->next();
            }
            $this->validateRowsForDuplicate(self::TABLE_TIER_PRICE);
        }
        return parent::_saveValidatedBunches();
    }

    /**
     * Validate all row data with existing prices in the database for duplicate
     *
     * A row is considered a duplicate if the pair (product_id, all_groups, customer_group_id, qty) exists for
     * both global and website scopes. And the base currency is the same for both global and website scopes.
     */
    private function validateRowsForDuplicate(string $table): void
    {
        if (!empty($this->allProductIds)) {
            $priceDataCollection = $this->getPrices(array_keys($this->allProductIds), $table);
            $defaultBaseCurrency = $this->currencyResolver->getDefaultBaseCurrency();
            $websiteCodeBaseCurrencyMap = $this->currencyResolver->getWebsitesBaseCurrency();
            $websiteIdCodeMap = array_flip($this->_storeResolver->getWebsiteCodeToId());
            foreach ($priceDataCollection as $priceData) {
                $isDefaultScope = (int) $priceData['website_id'] === 0;
                $baseCurrency = $isDefaultScope
                    ? $defaultBaseCurrency
                    : $websiteCodeBaseCurrencyMap[$websiteIdCodeMap[$priceData['website_id']] ?? null] ?? null;
                $rowNums = [];
                $key = $this->getUniqueKey($priceData, $baseCurrency);
                if ($isDefaultScope) {
                    if (isset($this->websiteScopeTierPrice[$key])) {
                        $rowNums = $this->websiteScopeTierPrice[$key];
                    }
                } else {
                    if (isset($this->globalScopeTierPrice[$key])) {
                        $rowNums = $this->globalScopeTierPrice[$key];
                    }
                }
                foreach ($rowNums as $rowNum) {
                    $this->addRowError(self::ERROR_DUPLICATE_TIER_PRICE, $rowNum);
                }
            }
        }
    }

    /**
     * Validate row data for duplicate
     *
     * A row is considered a duplicate if the pair (product_id, all_groups, customer_group_id, qty) exists for
     * both global and website scopes. And the base currency is the same for both global and website scopes.
     */
    private function validateRowForDuplicate(array $rowData, int $rowNum): void
    {
        $productId = $this->retrieveOldSkus()[$rowData[self::COL_SKU]] ?? null;
        if ($productId && !$this->_catalogData->isPriceGlobal()) {
            $productEntityLinkField = $this->getProductEntityLinkField();
            $priceData = [
                $productEntityLinkField => $productId,
                'website_id' => (int) $this->getWebSiteId($rowData[self::COL_TIER_PRICE_WEBSITE]),
                'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS ? 1 : 0,
                'customer_group_id' => $this->getCustomerGroupId($rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]),
                'qty' => $rowData[self::COL_TIER_PRICE_QTY],
            ];
            $defaultBaseCurrency = $this->currencyResolver->getDefaultBaseCurrency();
            $websiteCodeBaseCurrencyMap = $this->currencyResolver->getWebsitesBaseCurrency();
            $websiteIdCodeMap = array_flip($this->_storeResolver->getWebsiteCodeToId());
            $baseCurrency = $priceData['website_id'] === 0
                ? $defaultBaseCurrency
                : $websiteCodeBaseCurrencyMap[$websiteIdCodeMap[$priceData['website_id']] ?? null] ?? null;

            $this->allProductIds[$productId][] = $rowNum;
            $key = $this->getUniqueKey($priceData, $baseCurrency);
            if ($priceData['website_id'] === 0) {
                $this->globalScopeTierPrice[$key][] = $rowNum;
                if (isset($this->websiteScopeTierPrice[$key])) {
                    $this->addRowError(self::ERROR_DUPLICATE_TIER_PRICE, $rowNum);
                }
            } else {
                $this->websiteScopeTierPrice[$key][] = $rowNum;
                if (isset($this->globalScopeTierPrice[$key])) {
                    $this->addRowError(self::ERROR_DUPLICATE_TIER_PRICE, $rowNum);
                }
            }
        }
    }

    /**
     * Get the unique key of provided price
     */
    private function getUniqueKey(array $priceData, string $baseCurrency): string
    {
        $productEntityLinkField = $this->getProductEntityLinkField();
        return sprintf(
            '%s-%s-%s-%s-%.4f',
            $baseCurrency,
            $priceData[$productEntityLinkField],
            $priceData['all_groups'],
            $priceData['customer_group_id'],
            $priceData['qty']
        );
    }

    /**
     * Get existing prices in the database
     *
     * @param int[] $productIds
     * @return array
     */
    private function getPrices(array $productIds, string $table)
    {
        $productEntityLinkField = $this->getProductEntityLinkField();
        return $this->_connection->fetchAll(
            $this->_connection->select()
                ->from(
                    $this->_resourceFactory->create()->getTable($table),
                    [
                        $productEntityLinkField,
                        'all_groups',
                        'customer_group_id',
                        'qty',
                        'website_id',
                    ]
                )
                ->where(
                    $productEntityLinkField . ' IN (?)',
                    $productIds
                )
        );
    }
}
