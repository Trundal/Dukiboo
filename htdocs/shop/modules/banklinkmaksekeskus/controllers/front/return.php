<?php
/**
 * 
 *  Copyright 2013 Aktsiamaailm OÜ
 *  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents.
 *  

 */
class BanklinkmaksekeskusReturnModuleFrontController extends ModuleFrontController {

    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        parent::initContent();

        $result = $this->module->validatePayment($_REQUEST);
        if ($result['status'] == 'success') {
            $this->_setOrderState($result['data'], Configuration::get('PS_OS_PAYMENT'), true);
            if ($result['auto']) {
                echo $result['auto'];
                exit;
            }
            //redirect to proper page
            if (!Context::getContext()->customer->isLogged()) {
                Tools::redirect('index.php?controller=guest-tracking');
            } else {
                Tools::redirect('index.php?controller=history');
            }
        } else if ($result['status'] == 'cancelled') {
            //cancel order
            $this->_setOrderState($result['data'], Configuration::get('PS_OS_CANCELED'), false);
            //re-init cart
            if (Configuration::get('PS_ORDER_PROCESS_TYPE') == '1') {
                //onepage checkout enabled
                Tools::redirect('index.php?controller=order-opc&submitReorder&id_order='.intval($result['data']));
            } else {
                Tools::redirect('index.php?controller=order&submitReorder&id_order='.intval($result['data']));
            }
        } else {
            //redirect i do not know where
            Tools::redirect('index.php');
        }
    }
    
    
    private function _setOrderState($orderNr, $newState, $email = false) {
            $order = new Order(intval($orderNr));
            if ($order->getCurrentState() == Configuration::get('PS_OS_BANKWIRE')) {

                $new_history = new OrderHistory();
                $new_history->id_order = intval($order->id);
                $new_history->changeIdOrderState($newState, intval($order->id));
                if ($email) {
                    $new_history->addWithemail(true, array());
                    
                } else {
                    $new_history->add(true, array());
                }
            }
        
    }

}