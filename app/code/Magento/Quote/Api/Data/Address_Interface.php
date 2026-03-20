<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface AddressInterface
 * @api
 * @since 100.0.2
 */
interface AddressInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    public const KEY_EMAIL = 'email';

    public const KEY_COUNTRY_ID = 'country_id';

    public const KEY_ID = 'id';

    public const KEY_REGION_ID = 'region_id';

    public const KEY_REGION_CODE = 'region_code';

    public const KEY_REGION = 'region';

    public const KEY_CUSTOMER_ID = 'customer_id';

    public const KEY_STREET = 'street';

    public const KEY_COMPANY = 'company';

    public const KEY_TELEPHONE = 'telephone';

    public const KEY_FAX = 'fax';

    public const KEY_POSTCODE = 'postcode';

    public const KEY_CITY = 'city';

    public const KEY_FIRSTNAME = 'firstname';

    public const KEY_LASTNAME = 'lastname';

    public const KEY_MIDDLENAME = 'middlename';

    public const KEY_PREFIX = 'prefix';

    public const KEY_SUFFIX = 'suffix';

    public const KEY_VAT_ID = 'vat_id';

    public const SAME_AS_BILLING = 'same_as_billing';

    public const CUSTOMER_ADDRESS_ID = 'customer_address_id';

    public const SAVE_IN_ADDRESS_BOOK = 'save_in_address_book';

    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this|null
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId);

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set billing/shipping email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get same as billing flag
     *
     * @return int|null
     */
    public function getSameAsBilling();

    /**
     * Set same as billing flag
     *
     * @param int $sameAsBilling
     * @return $this
     */
    public function setSameAsBilling($sameAsBilling);

    /**
     * Get customer address id
     *
     * @return int|null
     */
    public function getCustomerAddressId();

    /**
     * Set customer address id
     *
     * @param int|null $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId);

    /**
     * Get save in address book flag
     *
     * @return int|null
     */
    public function getSaveInAddressBook();

    /**
     * Set save in address book flag
     *
     * @param int|null $saveInAddressBook
     * @return $this
     */
    public function setSaveInAddressBook($saveInAddressBook);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\AddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes);
}
