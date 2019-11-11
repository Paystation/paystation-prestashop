<?php

class PaystationErrorModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $order = OrderCore::getOrderByCartId($_GET['cart_id']);
        $retry_link = _PS_BASE_URL_ . __PS_BASE_URI__ . "order?submitReorder=&id_order=" . $order;

        $this->context->smarty->assign(array('error_message' => $_GET['msg'], 'retry_link' => $retry_link));
        $this->setTemplate('payment_error.tpl');
    }
}
