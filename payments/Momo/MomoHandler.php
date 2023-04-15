<?php
require_once('../../models/Model.php');

class MomoHandler extends Model
{
    private $model;
    public function __construct()
    {
        $this->model = new Model();
    }
    public function checkOrderPayment($orderId)
    {
        try {
            $this->model->setTable('Orders');
            return $this->model->getBy(['orderId' => $orderId], ['status', 'cost', 'paid', 'paymentMethod', 'paymentDate']);
        } catch (Exception $e) {
            return [];
        }
    }
    public function updateOrderPaid($orderId, $time)
    {
        if ($time == null)
            $time = date('Y-m-d H:i:s');
        $this->model->setTable('Orders');
        return $this->model->updateOne(['orderId' => (int)$orderId], ['paid' => 1, 'paymentMethod' => 'Momo Pay', 'paymentDate' => $time]);
    }
}
