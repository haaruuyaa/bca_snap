<?php

namespace Haaruuyaa\BcaSnap\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Haaruuyaa\BcaSnap\Services\BcaSnapServices;

class BcaSnapController
{
    public BcaSnapServices $services;
    public function __construct() {
        $this->services = new BcaSnapServices();
    }

    /**
     * The method to get balance from BCA API
     * @param $accNo
     * @return array
     * @throws GuzzleException
     */
    public function getBalance($accNo): array
    {
        return $this->services->balance($accNo);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $accNo
     * @return array
     */
    public function getStatement($startDate, $endDate, $accNo): array
    {
        return $this->services->statement($startDate, $endDate, $accNo);
    }

    /**
     * @param string $transactionId
     * @param string $beneficiaryAccountNo
     * @param string $amount
     * @param string $currency
     * @param string $remark1
     * @param string $remark2
     * @param string $additionalInfo
     * @param string $purposeCode
     * @return mixed
     */
    public function postTransferToBCA(string $transactionId, string $beneficiaryAccountNo, string $amount, string $currency, string $remark1, string $remark2, string $additionalInfo, string $purposeCode): mixed
    {
        return $this->services->transferToBCA($transactionId, $beneficiaryAccountNo, $amount, $currency, $remark1, $remark2, $additionalInfo, $purposeCode);
    }

    /**
     * @param string $originalTrxId
     * @return array
     */
    public function getTransferInquiry(string $originalTrxId): array
    {
        return $this->services->transferInquiryBCA($originalTrxId);
    }

    /**
     * @param string $transactionId
     * @param string $amount
     * @param string $customerNo
     * @param string $sourceAccNo
     * @return array
     */
    public function postTransferToBCAVirtualAccount(string $transactionId, string $amount, string $customerNo, string $sourceAccNo): array
    {
        return $this->services->transferToBCAVirtualAccount($transactionId, $amount, $customerNo, $sourceAccNo);
    }
}
