<?php
/**
	Käesoleva loomingu autoriõigused kuuluvad Matis Halmannile ja Aktsiamaailm OÜ-le
	Litsentsitingimused on saadaval http://www.e-abi.ee/litsents
 */
class BanklinkmaksekeskusPaymentModuleFrontController extends ModuleFrontController {

    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = true;
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int) $cart->id_currency),
            'description' => htmlspecialchars($this->module->l($this->module->getDescription())),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/'
        ));
        if ($this->module->ver == '1.5') {
            $this->setTemplate('payment_execution.tpl');
        } else {
            $this->setTemplate('payment_exec.tpl');
        }

    }

}