<?php

namespace Haaruuyaa\BcaSnap\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Haaruuyaa\BcaSnap\Repositories\BcaSnapRepositories;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BcaSnapServices
{

    private object $bankConfig;
    private object $vaConfig;
    private BCASnapRepositories $transaction;

    public function __construct()
    {
        $this->bankConfig = (object)config('bca.bank');

        $this->vaConfig = (object)config('bca.va');

        $this->transaction = new BCASnapRepositories();
    }

    /**
     * @param string $accountNo
     * @return mixed
     * @throws GuzzleException
     */
    public function balance(string $accountNo)
    {
        $method = 'POST';
        $uri = '/openapi/v1.0/balance-inquiry';
        $fullUrl = $this->bankConfig->url.':'.$this->bankConfig->port.$uri;
        $token = $this->getCredentials($this->bankConfig->client,$this->bankConfig->port);
        $transactionId = 'BAL'.date('YmdHis').random_int(1000,9999);

        $body = [
            'partnerReferenceNo' => $transactionId,
            'accountNo' => $accountNo
        ];

        $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body,$this->bankConfig->secret);

        $additionalHeader = [
            'CHANNEL-ID' => $this->bankConfig->channel,
            'X-PARTNER-ID' => $this->bankConfig->partner
        ];

        $headers = array_merge($prepareHeader, $additionalHeader);

        return $this->postApi($method, $fullUrl, $headers, $body);
    }

    public function statement(string $startDate, string $endDate, string $accNumber)
    {
        $method = 'POST';
        $uri = '/openapi/v1.0/bank-statement';
        $fullUrl = $this->bankConfig->url.':'.$this->bankConfig->port.$uri;
        $token = $this->getCredentials($this->bankConfig->client,$this->bankConfig->port);
        $transactionId = 'STMT'.date('YmdHis').random_int(1000,9999);

        $startDateDT = new \DateTime($startDate);
        $endDateDT = new \DateTime($endDate);
        $startDateConverted = $startDateDT->format(DATE_ATOM);
        $endDateConverted = $endDateDT->format(DATE_ATOM);

        $body = [
            'partnerReferenceNo' => $transactionId,
            'accountNo' => $accNumber,
            'fromDateTime' => $startDateConverted,
            'toDateTime' => $endDateConverted
        ];

        $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body, $this->bankConfig->secret);

        $additionalHeader = [
            'CHANNEL-ID' => $this->bankConfig->channel,
            'X-PARTNER-ID' => $this->bankConfig->partner
        ];

        $headers = array_merge($prepareHeader, $additionalHeader);

        return $this->postApi($method, $fullUrl, $headers, $body);
    }

    public function transferToBCA(string $transactionId, string $beneficiaryAccountNo, string $amount, string $currency, string $remark1, string $remark2, string $additionalInfo, string $purposeCode)
    {
        $method = 'POST';
        $uri = '/openapi/v1.0/transfer-intrabank';
        $fullUrl = $this->bankConfig->url.':'.$this->bankConfig->port.$uri;
        $token = $this->getCredentials($this->bankConfig->client,$this->bankConfig->port);
        $amountValue = number_format($amount,2,thousands_separator: '');
        $fullRemark = substr($remark1.' '.$remark2,0,36);

        $body = [
            "partnerReferenceNo" => $transactionId,
            "amount" => [
                "value" => $amountValue,
                "currency" => $currency
            ],
            "beneficiaryAccountNo" => $beneficiaryAccountNo,
            "remark" => $fullRemark,
            "sourceAccountNo" => env('BCA_SOURCE_ACC_NO'),
            "transactionDate" => date('c'),
            "additionalInfo" => [
                "economicActivity" => "",
                "transactionPurpose" => ""
            ]
        ];

        if($currency !== 'IDR') {
            $body['additionalInfo'] = [
                "economicActivity" => $additionalInfo,
                "transactionPurpose" => $purposeCode
            ];
        }

        $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body, $this->bankConfig->secret);

        $additionalHeader = [
            'CHANNEL-ID' => $this->bankConfig->channel,
            'X-PARTNER-ID' => $this->bankConfig->partner
        ];

        $headers = array_merge($prepareHeader, $additionalHeader);

        $insertData = [
            'type' => 'transfer-intrabank',
            'body' => $body,
            'header' => $headers,
            'remark_1' => $remark1,
            'remark_2' => $remark2
        ];

        $this->transaction->insert($insertData);

        $results = $this->postApi($method, $fullUrl, $headers, $body);

        $this->transaction->update($results);

        return $results;
    }

    public function transferInquiryBCA(string $originalTrxId)
    {
        $method = 'POST';
        $uri = '/openapi/v1.0/transfer/status';
        $fullUrl = $this->bankConfig->url.':'.$this->bankConfig->port.$uri;
        $token = $this->getCredentials($this->bankConfig->client,$this->bankConfig->port);
        $dataTransaction = $this->transaction->findByTrxId($originalTrxId);

        $serviceCode = match ($dataTransaction->type) {
            'transfer-intrabank' => '17',
            'transfer-interbank' => '18',
            'transfer-interbank-rtgs' => '22',
            'transfer-interbank-skn' => '23',
            'transfer-va' => '33',
            default => '17',
        };

        $body = [
            "originalPartnerReferenceNo" => $dataTransaction->trx_id,
            "originalReferenceNo" => $dataTransaction->ref_no,
            "originalExternalId" => $dataTransaction->external_id,
            "serviceCode" => $serviceCode,
            "transactionDate" => date('c')
        ];

        $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body, $this->bankConfig->secret);

        $additionalHeader = [
            'CHANNEL-ID' => $this->bankConfig->channel,
            'X-PARTNER-ID' => $this->bankConfig->partner
        ];

        if($serviceCode === '33') {
            $fullUrl = $this->vaConfig->url.':'.$this->vaConfig->port.$uri;
            $token = $this->getCredentials($this->vaConfig->client,$this->vaConfig->port);
            $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body, $this->vaConfig->secret);
            $additionalHeader = [
                'CHANNEL-ID' => $this->vaConfig->channel,
                'X-PARTNER-ID' => $this->vaConfig->partner
            ];
        }

        $headers = array_merge($prepareHeader, $additionalHeader);

        return $this->postApi($method, $fullUrl, $headers, $body);

    }

    public function transferToBCAVirtualAccount(string $transactionId, string $amount, string $customerNo, string $sourceAccNo)
    {
        $method = 'POST';
        $uri = '/openapi/v1.0/transfer-va/payment-intrabank';
        $fullUrl = $this->vaConfig->url.':'.$this->vaConfig->port.$uri;
        $token = $this->getCredentials($this->vaConfig->client,$this->vaConfig->port);
        $amountValue = number_format($amount,2,thousands_separator: '');

        $partnerServiceId = env('BCA_PARTNER_SERVICE_ID');
        $paddedPartnerServiceId = str_pad($partnerServiceId, 8,'0',STR_PAD_LEFT);
        $virtualAccNo = $paddedPartnerServiceId.$customerNo;

        $body = [
            'partnerReferenceNo' => $transactionId,
            'virtualAccountNo' => $virtualAccNo,
            "paidAmount" => [
                "value" => $amountValue,
                "currency" => "IDR"
            ],
            "trxDateTime" => date('c'),
            'sourceAccountNo' => $sourceAccNo
        ];

        $prepareHeader = $this->getSnapHeader($method, $fullUrl, $token, $body, $this->vaConfig->secret);

        $additionalHeader = [
            'CHANNEL-ID' => $this->vaConfig->channel,
            'X-PARTNER-ID' => $this->vaConfig->partner
        ];

        $headers = array_merge($prepareHeader, $additionalHeader);

        $insertData = [
            'type' => 'transfer-va',
            'body' => $body,
            'header' => $headers
        ];

        $this->transaction->insert($insertData);

        $result =  $this->postApi($method, $fullUrl, $headers, $body);

        $this->transaction->update($result);

        return $result;

    }

    private function getSnapHeader($method, $url, $token, $body, $clientSecret)
    {
        $timestamp = date('c');

        $encodedUri = $this->customUrlencode($url);

        $signature = $this->generateSymmetricSignature($method, $encodedUri, $token, $body, $timestamp, $clientSecret);

        return $this->getSnapHeaders($token, $timestamp, $signature);

    }

    private function getSnapHeaders($token, $timestamp, $signature)
    {
        $nonce = time().random_int(10000,99999);

        return [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-EXTERNAL-ID' => $nonce
        ];
    }

    private function getCredentials(string $clientId, string $port)
    {
        $timestamp = date('c');
        $uri = '/openapi/v1.0/access-token/b2b';
        $url = env('BCA_API_URL').':'.$port;
        $fullUrl = $url.$uri;

        $signature = $this->asymmetricSignature($timestamp, $clientId);

        $body = json_encode(['grantType' => "client_credentials"]);

        try {

            $client = new Client();
            $request = new GuzzleRequest('post',$fullUrl,[
                'X-TIMESTAMP' => $timestamp,
                'X-CLIENT-KEY' => $clientId,
                'Content-Type' => 'application/json',
                'X-SIGNATURE' => $signature
            ],$body);
            $results = $client->send($request);
            $response = json_decode($results->getBody()->getContents(),true);

            if($response['responseCode'] === '2007300') {
                return $response['accessToken'];
            }

            return false;

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().'|'.$ex->getFile(). ' => '.$ex->getLine());
            return false;
        }
    }

    /**
     * @param string $timestamp
     * @param string $clientId
     * @return string|bool
     */
    private function asymmetricSignature(string $timestamp, string $clientId): string|bool
    {
        try {
            $privateKey = openssl_pkey_get_private(Storage::get(env('BCA_PRIVATE_KEY_PATH')));

            $stringToSign = $clientId .'|'. $timestamp;

            openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            return base64_encode($signature);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().'|'.$ex->getFile(). ' => '.$ex->getLine());
            return false;
        }
    }

    /**
     * @param $method
     * @param $uri
     * @param $token
     * @param $body
     * @param $timestamp
     * @param $clientSecret
     * @return string
     */
    private function generateSymmetricSignature($method, $uri, $token, $body, $timestamp, $clientSecret)
    {
        if(empty($body)) {
            $body = '';
        } else {
            $body = json_encode($body, JSON_UNESCAPED_SLASHES);
        }

        $shaBody = hash('sha256', $body);

        $stringToSign = $method.':'.$uri.':'.$token.':'.$shaBody.':'.$timestamp;


        return base64_encode(hash_hmac('sha512', $stringToSign, $clientSecret, true));
    }

    /**
     * @param $method
     * @param $url
     * @param $headers
     * @param $body
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postApi($method, $url, $headers, $body)
    {
        try {
            $client = new Client();
            $req = new GuzzleRequest($method,$url,$headers,json_encode($body));
            $results = $client->send($req);
            $response = $results->getBody()->getContents();

            return json_decode($response, true);

        } catch (RequestException $ex) {
            return json_decode($ex->getResponse()?->getBody()->getContents(), true);
        }
    }

    /**
     * @param $url
     * @return string
     */
    private function customUrlencode($url)
    {
        // Parse the URL into its components
        $urlParts = parse_url($url);

        // Handle path component
        $path = $urlParts['path'] ?? '';
        $path = implode('/', array_map('rawurlencode', explode('/', $path)));

        // Handle query component
        $query = $urlParts['query'] ?? '';
        parse_str($query, $queryParams);
        ksort($queryParams); // Sort parameters lexicographically
        $query = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

        // Reassemble the URL
        if ($query !== '') {
            $path .= '?' . $query;
        }

        return $path;
    }
}
