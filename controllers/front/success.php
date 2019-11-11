<?php

class PaystationSuccessModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array('amount' => ($_GET['am'] / 100), 'order_id' => $_GET['order_id'],));
        $this->setTemplate('payment_success.tpl');
    }
}

