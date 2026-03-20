<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Cardinal_Commerce\Model\Response;

use Magento\Framework\Intl\Date_Time_Factory;
/**
 * Validates payload of CardinalCommerce response JWT.
 */
class Jwt_Payload_Validator implements Jwt_Payload_Validator_Interface
{
    /**
     * Resulting state of the transaction.
     *
     *  SUCCESS  - The transaction resulted in success for the payment type used. For example,
     *             with a CCA transaction this would indicate the user has successfully completed authentication.
     *
     *  NOACTION - The transaction was successful but requires in no additional action. For example,
     *             with a CCA transaction this would indicate that the user is not currently enrolled in 3D Secure,
     *             but the API calls were successful.
     *
     *  FAILURE  - The transaction resulted in an error. For example, with a CCA transaction this would indicate
     *             that the user failed authentication or an error was encountered while processing the transaction.
     *
     *  ERROR    - A service level error was encountered. These are generally reserved for connectivity
     *             or API authentication issues. For example if your JWT was incorrectly signed, or Cardinal
     *             services are currently unreachable.
     *
     * @var array
     */
    private $allowed_action_code = ['SUCCESS', 'NOACTION'];
    /**
     * 3DS status of transaction from ECI Flag value. Liability shift applies.
     *
     *  05 - Successful 3D Authentication (Visa, AMEX, JCB)
     *  02 - Successful 3D Authentication (MasterCard)
     *  06 - Attempted Processing or User Not Enrolled (Visa, AMEX, JCB)
     *  01 - Attempted Processing or User Not Enrolled (MasterCard)
     *  07 - 3DS authentication is either failed or could not be attempted;
     *       possible reasons being both card and Issuing Bank are not secured by 3DS,
     *       technical errors, or improper configuration. (Visa, AMEX, JCB)
     *  00 - 3DS authentication is either failed or could not be attempted;
     *       possible reasons being both card and Issuing Bank are not secured by 3DS,
     *       technical errors, or improper configuration. (MasterCard)
     *
     * @var array
     */
    private $allowed_eci_flag = ['05', '02', '06', '01'];
    /**
     * @var DateTimeFactory
     */
    private $date_time_factory;
    /**
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(Date_Time_Factory $date_time_factory)
    {
        $this->date_time_factory = $date_time_factory;
    }
    /**
     * @inheritdoc
     */
    public function validate(array $jwt_payload): bool
    {
        $transaction_state = $jwt_payload['Payload']['ActionCode'] ?? '';
        $error_number = $jwt_payload['Payload']['ErrorNumber'] ?? -1;
        $eci_flag = $jwt_payload['Payload']['Payment']['ExtendedData']['ECIFlag'] ?? '';
        $exp_timestamp = $jwt_payload['exp'] ?? 0;
        return $this->is_valid_error_number((int) $error_number) && $this->is_valid_transaction_state($transaction_state) && $this->is_valid_eci_flag($eci_flag) && $this->is_not_expired((int) $exp_timestamp);
    }
    /**
     * Checks application error number.
     *
     * A non-zero value represents the error encountered while attempting the process the message request.
     *
     * @param int $errorNumber
     * @return bool
     */
    private function is_valid_error_number(int $error_number)
    {
        return $error_number === 0;
    }
    /**
     * Checks if value of transaction state identifier is in allowed list.
     *
     * @param string $transactionState
     * @return bool
     */
    private function is_valid_transaction_state(string $transaction_state)
    {
        return in_array($transaction_state, $this->allowed_action_code);
    }
    /**
     * Checks if value of ECI Flag identifier is in allowed list.
     *
     * @param string $eciFlag
     * @return bool
     */
    private function is_valid_eci_flag(string $eci_flag)
    {
        return in_array($eci_flag, $this->allowed_eci_flag);
    }
    /**
     * Checks if token is not expired.
     *
     * @param int $expTimestamp
     * @return bool
     */
    private function is_not_expired(int $exp_timestamp)
    {
        $current_date = $this->date_time_factory->create('now', new \DateTimeZone('UTC'));
        return $current_date->get_timestamp() < $exp_timestamp;
    }
}