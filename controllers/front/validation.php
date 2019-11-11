<?php

class PaystationValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = new Cart((int)$_GET['merchant_ref']);

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'paystation') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) die($this->module->l('This payment method is not available.', 'validation'));

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) Tools::redirect('index.php?controller=order&step=1');

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        $paystationID = Configuration::get('paystation_PaystationID');

        if (isset($_GET['em'])) {
            $error_message = $_GET['em'];
            if ($_GET['ec'] == '0') {
                $responseCode = $this->_transactionVerification($paystationID, $_GET['ti'], $_GET['ms']);

                if ((int)$responseCode == 0) $success = true;
                else {
                    $success = false;
                    $error_message = "Response code does not match";
                }
            } else $success = false;
        } else $success = false;

        $mailVars = array();

        if ($success == true) {
            if (!$cart->OrderExists()) {
                $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'),
                    $total, 'Paystation Payment Gateway', NULL, $mailVars,
                    (int)$currency->id, false, false);
            }
            Tools::redirect('index.php?fc=module&module=paystation&controller=success&am='
                . $_GET['am'] . '&order_id=' . $this->module->currentOrder->reference);
        } else {
            if (!$cart->OrderExists()) {
                $this->module->validateOrder((int)$cart->id,
                    Configuration::get('PS_OS_ERROR'), $total,
                    'Paystation Payment Gateway', NULL, $mailVars, (int)$currency->id,
                    false, false);//$customer->secure_key);
            }
            Tools::redirect('index.php?fc=module&module=paystation&controller=error&msg=' . $error_message . "&cart_id=" . $cart->id);

        }
    }

    private function _transactionVerification($paystationID, $transactionID, $merchantSession)
    {
        $transactionVerified = '';
        $lookupXML = $this->_quickLookup($paystationID, 'ms', $merchantSession);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $lookupXML, $vals, $tags);
        xml_parser_free($p);
        foreach ($tags as $key => $val) {
            if ($key == "PAYSTATIONERRORCODE") {
                for ($i = 0; $i < count($val); $i++) {
                    $responseCode = $this->_parseCode($vals);
                    $transactionVerified = $responseCode;
                }
            } else continue;
        }

        return $transactionVerified;
    }

    private function _quickLookup($pi, $type, $value)
    {
        $url = "https://www.paystation.co.nz/lookup/quick/?pi=$pi&$type=$value";

        $defined_vars = get_defined_vars();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function _parseCode($mvalues)
    {
        $result = '';
        for ($i = 0; $i < count($mvalues); $i++) {
            if (!strcmp($mvalues[$i]["tag"], "QSIRESPONSECODE") && isset($mvalues[$i]["value"])) {
                $result = $mvalues[$i]["value"];
            }
        }
        return $result;
    }
}
