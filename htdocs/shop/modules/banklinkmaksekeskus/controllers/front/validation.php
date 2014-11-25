<?php

/**
  Käesoleva loomingu autoriõigused kuuluvad Matis Halmannile ja Aktsiamaailm OÜ-le
  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents
 */
class BanklinkmaksekeskusValidationModuleFrontController extends ModuleFrontController {

    public function postProcess() {
        $cart = $this->context->cart;

        $currency = $this->context->currency;
        $total = floatval(number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', ''));
        $curr = new Currency($cart->id_currency);
//convert to proper currency
        $convertedPrice = Tools::convertPrice($total / $curr->conversion_rate, $currency);
        $mailVars = array(
            '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
            '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
            '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS')),
        );

        $bankwire = $this->module;
        $bankwire->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $bankwire->displayName, NULL, $mailVars, $currency->id, false, $cart->secure_key);
        $order = new Order($bankwire->currentOrder);
//in here goes the payment start info

        $result = $bankwire->startPayment($convertedPrice, $order->id, $currency->iso_code);
//var_dump($order->id);
//var_dump($orderParams);
        $str = '---';
        $post = '<form action="' . $result['destination'] . '" method="post" name="formk" id="formk">';
        foreach ($result['params'] as $k => $v) {
//    		$r .= "&".$k."=".urlencode($v);
            $post .= '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v) . '" />' . "\r\n";
        }
        $post .= '<input type="submit" name="submi" value="' . $str . '"/>';
        $post .= "</form>";
        $post .= '<script type="text/javascript">' . "\r\n";
        $post .= "<!--\r\n";
        $post .= "
function subForm() {
	var frm = document.getElementById(\"formk\");
        
	frm.submit();
}
window.onload = subForm;
";
        $post .= "// -->\r\n";
        $post .= '</script>' . "\r\n";
        echo $post;
    }

}