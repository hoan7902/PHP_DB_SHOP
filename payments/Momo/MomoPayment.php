<?php

require_once('../../config/MomoConfig.php');

class MomoPayment
{
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $notifyUrl;
    private $returnUrl;
    private $endpoint;
    public function __construct($config = MOMOCONFIG)
    {
        $this->partnerCode = MOMOCONFIG['partnerCode'];
        $this->accessKey = MOMOCONFIG['accessKey'];
        $this->secretKey = MOMOCONFIG['secretKey'];
        $this->notifyUrl = MOMOCONFIG['notifyUrl'];
        $this->returnUrl = MOMOCONFIG['returnUrl'];
        $this->endpoint = MOMOCONFIG['endpoint'];
    }
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
    public function initPayment($orderId, $amount, $orderInfo = "Thanh toán qua MoMo")
    {
        $extraData = "order=$orderId";
        $uniqOrderId = "ORDER_" . $orderId . "_" . time();
        $requestId = time() . "";
        $requestType = "captureMoMoWallet";
        $rawHash = "partnerCode=" . $this->partnerCode . "&accessKey=" . $this->accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $uniqOrderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $this->returnUrl . "&notifyUrl=" . $this->notifyUrl . "&extraData=" . $extraData;
        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);
        $data = array(
            'partnerCode' => $this->partnerCode,
            'accessKey' => $this->accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $uniqOrderId,
            'orderInfo' => $orderInfo,
            'returnUrl' => $this->returnUrl,
            'notifyUrl' => $this->notifyUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($this->endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);
        print_r($jsonResult);
        header('Location: ' . $jsonResult['payUrl']);
    }
}
