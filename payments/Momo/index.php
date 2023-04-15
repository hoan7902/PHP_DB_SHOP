<?php
require_once('./MomoPayment.php');
require_once('./MomoHandler.php');
require_once('./../../config/MomoConfig.php');

function alertBox($message)
{
    echo "<script>alert('$message');</script>";
}

function ConfirmBox($message)
{
    echo "<script>confirm('$message');</script>";
}

$orderId = isset($_GET['orderId']) ? $_GET['orderId'] : null;
if ($orderId === null || (int)$orderId < 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Payment Error!!']);
} else {
    $orderId = (int)$orderId;
    $momoHandler = new MomoHandler();
    $checkOrder = $momoHandler->checkOrderPayment($orderId);
    if (count($checkOrder) > 0) {
        $order = $checkOrder[0];
        if ($order['status'] == 'Done' || $order['paid'] == true) {
            echo json_encode(['message' => 'Đơn hàng đã được thanh toán']);
        } else {
            $payment = new MomoPayment();
            $payment->initPayment($orderId, /* $order['cost'] */ "1000", "Thanh toán bằng Momo");
        }
    } else {
        echo json_encode(['message' => 'Đơn hàng không tồn tại']);
    }
}
