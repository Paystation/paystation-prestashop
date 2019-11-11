<?php

class PaystationPostbackModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        ob_start();
        $postData = file_get_contents("php://input"); //Get the contents of the XML packet that has been POSTed back from Paystation.
        $xml = simplexml_load_string($postData);  // Create an XML string
        $headers = "MIME-Version: 1.0 \r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1 \r\n';

        if (!empty($xml)) {
            $errorCode = $xml->ec;
            $errorMessage = $xml->em;
            $transactionId = $xml->ti;
            $cardType = $xml->ct;
            $merchantReference = $xml->merchant_ref;
            $testMode = $xml->tm;
            $merchantSession = $xml->MerchantSession;
            $usedAcquirerMerchantId = $xml->UsedAcquirerMerchantID;
            $amount = $xml->PurchaseAmount; // Note this is in cents
            $transactionTime = $xml->TransactionTime;
            $requestIp = $xml->RequestIP;

            $message = "Error Code: " . $errorCode . "<br/>";
            $message .= "Error Message: " . $errorMessage . "<br/>";
            $message .= "Transaction ID: " . $transactionId . "<br/>";
            $message .= "Card Type: " . $cardType . "<br/>";
            $message .= "Merchant Reference: " . $merchantReference . "<br/>";
            $message .= "Test Mode: " . $testMode . "<br/>";
            $message .= "Merchant Session: " . $merchantSession . "<br/>";
            $message .= "Merchant ID: " . $usedAcquirerMerchantId . "<br/>";
            $message .= "Amount: " . $amount . " (cents)<br/>";
            $message .= "Transaction Time: " . $transactionTime . "<br/>";
            $message .= "IP: " . $requestIp . "<br/>";

            $subject = "Transaction Results for order " . $merchantReference;

            $cartObj = new Cart($merchantReference);

            if ($cartObj->id == null) $cart_found = false;
            else $cart_found = true;

            // if the order was not created, then validate.php was not called
            if ($cart_found && !$cartObj->OrderExists()) {

                $cartObj = new Cart($merchantReference);
                $cart_total = (float)$cartObj->getOrderTotal(true, Cart::BOTH);
                $paystation_amount = ((float)$amount) / 100.0;

                if (((int)($cart_total * 100)) == (int)((string)$amount)) { //check the amount on the cart matches the payment amount
                    $cart_found = true;
                    if (((int)$errorCode == 0)) {
                        $this->module->validateOrder((int)$merchantReference, Configuration::get('PS_OS_PAYMENT'), $paystation_amount, 'Paystation Payment Gateway - confirmed by Postback response', NULL);//, $mailVars, (int)$currency->id, false, $objCustomer->secure_key);
                    } else {
                        $this->module->validateOrder((int)$merchantReference, Configuration::get('PS_OS_ERROR'), $paystation_amount, 'Paystation Payment Gateway - confirmed by Postback response', NULL);//, $mailVars, (int)$currency->id, false, $objCustomer->secure_key);
                    }
                } else $cart_found = false;
            }
            if (!$cart_found && ((int)$errorCode == 0)) {

                $id_lang = 1;
                $contact_array = ContactCore::getContacts($id_lang);
                $prev_email = null;
                foreach ($contact_array as &$contact) {
                    if ($contact['customer_service'] == '1') {
                        $explanation = "<p>You are receiving this automated message because the Postback function for
										the Paystation payment module has received a postback response
										for a successful Paystation transaction, but could not match a Prestashop
										</p><p>
										shopping cart with the transaction.
										This means the postback function could not determine if an order has already
										been created to match this transaction.
										</p><p>
										Postback response details:
										</p><p>" . $message . "</p>";
                        if ($prev_email == null || $prev_email != $contact['email']) {
                            mail($contact['email'], 'Paystation Postback - Order Look Up Required', $explanation, $headers);
                            $prev_email = $contact['email'];
                        }
                    }
                }
            }
        }
        parent::initContent();
    }
}

