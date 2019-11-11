<?php

class paystation extends PaymentModule
{
    public function __construct()
    {
        parent::__construct();
        $this->name = 'paystation';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'Paystation';
        $this->need_instance = 0;
        $this->dependencies = array('blockcart');


        $this->displayName = $this->l('Paystation');
        $this->description = $this->l('Paystation three-party payment module.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->is_configurable = 1;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() || !$this->registerHook('payment') ||
            !$this->registerHook('paymentReturn') || !$this->registerHook('beforePayment')) return false;
        else return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('Paystation')) return false;
        else {
            return true;
        }
    }

    private function _displaySettingsForm()
    {
        $PaystationID = Configuration::get($this->name . '_PaystationID');
        $GatewayID = Configuration::get($this->name . '_GatewayID');
        $DisplayLabel = Configuration::get($this->name . '_DisplayLabel');
        $test = Configuration::get($this->name . '_TestMode');

        if ($test == 'T') $TestMode = 'checked';
        else $TestMode = '';
        $this->_html .= '
	    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
	    <table>
		  <tr><td><label for ="PaystationID">' . $this->l('Paystation ID:') . '</label> </td><td> <input value = "' . $PaystationID . '" type="text" name="PaystationID" /></td></tr>
	      <tr><td><label for ="GatewayID">' . $this->l('Gateway ID:') . '</label></td><td> <input value = "' . $GatewayID . '" type="text" name="GatewayID" /></td></tr>
		  <tr><td><label for ="DisplayLabel">' . $this->l('Display Label:') . '</label></td><td> <input value = "' . $DisplayLabel . '" type="text" name="DisplayLabel" /></td></tr>
		  <tr><td><label for ="TestMode">' . $this->l('Enable Test Mode:') . '</label></td><td> <input ' . $TestMode . ' type="checkbox" name="TestMode" /></td></tr>
		  <tr><td></td><td><input type="submit" name="submit" value="' . $this->l('Update Paystation Settings') . '" class="button" /></td></tr>
		</table>
	    </form>';
    }

    public function getContent()
    {
        // Called When the settings page is displayed
        if (Tools::isSubmit('submit')) {
            Configuration::updateValue($this->name . '_PaystationID', Tools::getValue('PaystationID'));
            Configuration::updateValue($this->name . '_GatewayID', Tools::getValue('GatewayID'));
            Configuration::updateValue($this->name . '_DisplayLabel', Tools::getValue('DisplayLabel'));

            if (Tools::getValue('TestMode')) $test = "T";
            else $test = "F";
            Configuration::updateValue($this->name . '_TestMode', $test);

        }
        $this->_displaySettingsForm();
        return $this->_html;
    }

    // Called when needing to build a list of the available payment solutions, during the order process.
    // Ideal location to enable the choice of a payment module that you have developed
    public function hookPayment($params)
    {
        $label = Configuration::get($this->name . '_DisplayLabel') . '.';
        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_cheque' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        $retval = $this->display(__FILE__, 'payment.tpl');
        $retval = str_replace('---', $label, $retval);
        return $retval;
    }
}