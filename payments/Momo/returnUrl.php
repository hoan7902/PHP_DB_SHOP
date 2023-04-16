<?php
header('Content-type: text/html; charset=utf-8');

require_once('./MomoPayment.php');
require_once('./MomoHandler.php');
require_once('../../config/MomoConfig.php');
require_once('./helpDisplay.php');

if (!empty($_GET)) {
    $partnerCode = $_GET["partnerCode"];
    $accessKey = $_GET["accessKey"];
    $orderId = $_GET["orderId"];
    $localMessage = $_GET["localMessage"];
    $message = $_GET["message"];
    $transId = $_GET["transId"];
    $orderInfo = $_GET["orderInfo"];
    $amount = $_GET["amount"];
    $errorCode = $_GET["errorCode"];
    $responseTime = $_GET["responseTime"];
    $requestId = $_GET["requestId"];
    $extraData = $_GET["extraData"];
    $payType = $_GET["payType"];
    $orderType = $_GET["orderType"];
    $extraData = $_GET["extraData"];
    $m2signature = $_GET["signature"];
    //Checksum
    $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
        "&orderType=" . $orderType . "&transId=" . $transId . "&message=" . $message . "&localMessage=" . $localMessage . "&responseTime=" . $responseTime . "&errorCode=" . $errorCode .
        "&payType=" . $payType . "&extraData=" . $extraData;


    $partnerSignature = hash_hmac("sha256", $rawHash, MOMOCONFIG['secretKey']);
    $orderID = null;
    $extraDataArr = explode('&', $extraData);
    foreach ($extraDataArr as $item) {
        $keyValue = explode('=', $item);
        if ($keyValue[0] === 'order') {
            $orderID = $keyValue[1];
            break;
        }
    }
    if ($m2signature == $partnerSignature) {
        if ($errorCode == '0') {
            // OK
            // Update data
            $momoHandler = new MomoHandler();
            try {
                $res = $momoHandler->updateOrderPaid($orderID, $responseTime);
                // echo json_encode(['status' => true, 'message' => 'Oke']);
                return render(true, null, null);
            } catch (Exception $e) {
                // echo json_encode(['status' => false, 'message' => $e->getMessage()]);
                return render(false, ['orderID' => $orderID], null);
            }
        } else {
            // echo json_encode(['status' => false, 'message' => 'Failed']);
            return render(false, ['orderID' => $orderID], null);
        }
    } else {
        return render(false, ['orderID' => $orderID], null);
    }
}
