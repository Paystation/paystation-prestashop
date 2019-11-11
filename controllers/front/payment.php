<?php

/**
 * @since 1.5.0
 */
class PaystationPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     * called when the Paystation payment method is selected in the shopping cart
     */
    public function initContent()
    {
        $cart = $this->context->cart;
        Tools::redirect($this->_redirect($cart));
    }

    private function _makePaystationSessionID($min = 8, $max = 8)
    {
        // seed the random number generator - straight from PHP manual
        $seed = (double)microtime() * getrandmax();
        srand($seed);

        $pass = '';
        // make a string of $max characters with ASCII values of 40-122
        $p = 0;
        while ($p < $max):
            $r = chr(123 - (rand() % 75));

            // get rid of all non-alphanumeric characters
            if (!($r >= 'a' && $r <= 'z') && !($r >= 'A' && $r <= 'Z') && !($r >= '1' && $r <= '9')) continue;
            $pass .= $r;

            $p++; endwhile;
        // if string is too short, remake it
        if (strlen($pass) < $min):
            $pass = $this->makePaystationSessionID($min, $max);
        endif;

        return $pass;
    }

    private function _redirect($cart)
    {
        $PaystationID = Configuration::get('paystation_PaystationID');
        $GatewayID = Configuration::get('paystation_GatewayID');
        $test = Configuration::get('paystation_TestMode');
        $total = $cart->getOrderTotal(true, Cart::BOTH);

        if ($test == 'T') $test_string = '&pstn_tm=t';
        else $test_string = '';

        $pstn_pi = $PaystationID;
        $pstn_gi = $GatewayID;
        $pstn_am = round($total * 100);
        $pstn_mr = $cart->id;
        $pstn_ms = $pstn_mr . '_' . time() . '-' . $this->_makePaystationSessionID(8, 8);

        $paystationURL = 'https://www.paystation.co.nz/direct/paystation.dll';
        $paystationParams = "paystation=_empty&pstn_nr=t&pstn_pi=$pstn_pi&pstn_gi=$pstn_gi&pstn_ms=$pstn_ms&pstn_am=$pstn_am&pstn_mr=$pstn_mr" . $test_string;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paystationParams);
        curl_setopt($ch, CURLOPT_URL, $paystationURL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $result, $vals, $tags);
        xml_parser_free($p);
        for ($j = 0; $j < count($vals); $j++) {
            if (!strcmp($vals[$j]["tag"], "TI") && isset($vals[$j]["value"])) {
                $returnTI = $vals[$j]["value"];
            }
            if (!strcmp($vals[$j]["tag"], "EC") && isset($vals[$j]["value"])) {
                $errorCode = $vals[$j]["value"];
            }
            if (!strcmp($vals[$j]["tag"], "DIGITALORDER") && isset($vals[$j]["value"])) {
                $digitalOrder = $vals[$j]["value"];
            }
        }

        if (isset($digitalOrder)) {
            return $digitalOrder;
        } else {
            var_dump($result);
            exit ("digitalOrder not set");
        }

        return $digitalOrder;
    }
}
