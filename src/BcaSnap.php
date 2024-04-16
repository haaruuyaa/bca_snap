<?php

namespace Haaruuyaa\BcaSnap;

use GuzzleHttp\Exception\GuzzleException;
use Haaruuyaa\BcaSnap\Controllers\BcaSnapController;

class BcaSnap
{
    public BcaSnapController $controller;
    public function __construct() {
        $this->controller = new BcaSnapController();
    }

    /**
     * @param string $accNo
     * @return array
     * @throws GuzzleException
     */
    public function balance(string $accNo): array
    {
        return $this->controller->getBalance($accNo);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $accNo
     * @return array
     */
    public function statement($startDate, $endDate, $accNo): array
    {
        return $this->controller->getStatement($startDate, $endDate, $accNo);
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
    public function transferToBCA(string $transactionId, string $beneficiaryAccountNo, string $amount, string $currency, string $remark1, string $remark2, string $additionalInfo, string $purposeCode): mixed
    {
        return $this->controller->postTransferToBCA($transactionId, $beneficiaryAccountNo, $amount, $currency, $remark1, $remark2, $additionalInfo, $purposeCode);
    }

    /**
     * @param string $originalTrxId
     * @return array
     */
    public function inquiry(string $originalTrxId): array
    {
        return $this->controller->getTransferInquiry($originalTrxId);
    }

    /**
     * @param string $transactionId
     * @param string $amount
     * @param string $customerNo
     * @param string $sourceAccNo
     * @return array
     */
    public function transferToBCAVirtualAccount(string $transactionId, string $amount, string $customerNo, string $sourceAccNo): array
    {
        return $this->controller->postTransferToBCAVirtualAccount($transactionId, $amount, $customerNo, $sourceAccNo);
    }
}
