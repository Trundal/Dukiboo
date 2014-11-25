<?php

/*
  
 *  Copyright 2013 Aktsiamaailm OÜ
 *  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents.
 *  

 */

/**
 * Description of banklinkmaksekeskus
 *
 * @author Matis
 */
class BanklinkMaksekeskus extends PaymentModule {
    
    private $_html = '';
    public $ver = '1.6';
    protected $_title;
    protected $_description;
    protected $_destination_url;
    protected $_shop_id;
    protected $_locale;
    protected $_api_secret;
    protected $_return;
    protected $_store_scope;
    
    protected $_config_prefix = 'BANKLINK_MAKSE_';
    
    protected $_config_array = array(
        'TITLE',
        'DESCRIPTION',
        'DESTINATION_URL',
        'SHOP_ID',
        'LOCALE',
        'API_SECRET',
    );
    
    protected $form_fields = array();
    
    private $_html_template = '<tr width="130" style="height: 35px;">
        <td>${LABEL}</td>
        <td>${INPUT}</td>
    </tr>';
    
    /**
     * <p>Is activated in admin when another store scope is selected</p>
     * @var int
     */
    protected $_id_store;
    
    /**
     * <p>Is activated in admin when another store scope is selected</p>
     * @var int
     */
    protected $_id_store_group;
    
    
    
    public function __construct() {
        if (substr(_PS_VERSION_, 0, 3) == "1.5") {
            $this->ver = "1.5";
        }
        $this->name = 'banklinkmaksekeskus';
        $this->tab = 'payments_gateways';
        
        $this->version = '0.2';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        $this->_id_store_group = null;
        $this->_id_store = null;
        $cookie = new Cookie('psAdmin');
        if ($cookie->id_employee) {
            $this->_id_store_group = Tools::getValue('id_store_group', '0');
            $this->_id_store = Tools::getValue('id_store', '0');
            
        }
        
        $config = Configuration::getMultiple($this->_getConfigArray($this->_config_prefix, $this->_config_array));
        $this->_initConfig($config);
        
        parent::__construct();
        
        $this->displayName = $this->l('Estonian Maksekeskus payment gateway');
        $this->description = $this->l('Accept payments from Estonian customers');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        if (!isset($this->_title) || !isset($this->_destination_url) || !isset($this->_shop_id) || !isset($this->_api_secret)) {
            $this->warning = $this->l('Details must be configured in order to use this module correctly');
        }
        if (!sizeof(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }
    }
    
    
    public function install() {
        if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('displayHeader') || !$this->registerHook('actionAdminControllerSetMedia')) {
            return false;
        }
        return true;
        
    }
    
    public function uninstall() {
        $config = $this->_getConfigArray($this->_config_prefix, $this->_config_array);
        //do not delete config
//        foreach  ($config as $configToRemove) {
//            if (!Configuration::deleteByName($configToRemove)) {
//                return false;
//            }
//        }
        if (!$this->unregisterHook('payment') || !$this->unregisterHook('paymentReturn') || !$this->unregisterHook('displayHeader')
                || !$this->unregisterHook('actionAdminControllerSetMedia')) {
            return false;
        }
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }
    
    private $_postErrors = array();
    
    private function _postValidation() {
        $data = $_POST;
        if (isset($data['btnSubmit'])) {
            
            $config = $this->_getConfigArray('', $this->_config_array);
            $formFields = $this->_initFormFields();
            foreach ($config as $configValue) {
                if (!isset($data[$configValue]) || empty($data[$configValue])) {
                    if ($configValue == 'DESCRIPTION') {
  
                        if (!isset($data[$configValue])) {
                            $this->_postErrors[] = sprintf($this->l('%s is required'), $formFields[strtolower($configValue)]['title']);
                        }
                        
                    } else {
                        $this->_postErrors[] = sprintf($this->l('%s is required'), $formFields[strtolower($configValue)]['title']);
                    }
                }
            }
        }
    }
    
    private function _postProcess() {
        $data = $_POST;
        if (isset($data['btnSubmit'])) {
            $config = $this->_getConfigArray('', $this->_config_array);
            foreach ($config as $configValue) {
                ConfigurationCore::updateValue($this->_config_prefix . $configValue, $data[$configValue], false
                        , Tools::getValue('id_store_group', '0')
                        , Tools::getValue('id_store', '0'));
            }
            $config = Configuration::getMultiple($this->_getConfigArray($this->_config_prefix, $this->_config_array));
            $this->_initConfig($config);
            
            $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="' . $this->l('ok') . '" /> ' . $this->l('Settings updated') . '</div>';
        }
    }
    
    private function _displayHeader() {
        $this->_html .= <<<HTML
   <img src="../modules/{$this->name}/{$this->name}.gif" alt=""  style="float:left; margin-right:15px;"/>
       <b>{$this->l('This module allows you to accept payments from Estonian customers')}</b>
           <br /><br />
           {$this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.')}
               <br />
               {$this->l('After successful payment, the order will change into \'Processing\' status.')}
                   <br />
                   <br />
                   <br />
HTML;
    }
    
    private function _displayForm() {
        //Tools::safeOutput($_SERVER['REQUEST_URI'])
        if (Configuration::getGlobalValue('PS_MULTISHOP_FEATURE_ACTIVE')) {
            $shopFields = array(
                'store_scope' => array(
                    'title' => $this->l('Store Scope'),
                    'type' => 'stores',
                    'description' => $this->l('Current configuration scope'),
                    'default' => '0',
                    'css' => 'width: 300px;',
                )
            );
            $formFields = array_merge($shopFields, $this->_initFormFields());
        } else {
            $formFields = $this->_initFormFields();
        }


        /*
         * 
            ),
         * 
         */
        $this->_html .= $this->_getFormHtml($_SERVER['REQUEST_URI'], 'post', $formFields);
    }
    
    
    /**
     * <p>Fetches global configuration for this instance.</p>
     * @param string $param
     * @return mixed
     */
    public function getGlobalConfigData($param) {
        $value = Configuration::getGlobalValue($this->_config_prefix . $param);
        return $value;
    }
    
    /**
     * <p>Fetches configuration for Admin form taking into account supplied store scope.</p>
     * @param string $param
     * @return mixed
     */
    public function getFormConfigData($param) {
        
        $value = Configuration::get($this->_config_prefix . $param, null, $this->_id_store_group, $this->_id_store);
        if ($value === null || $value === false) {
            $formFields = $this->_initFormFields();
            if (isset($formFields[strtolower($param)]) && $formFields[strtolower($param)]['default']) {
                return $formFields[strtolower($param)]['default'];
            }
        }
        return $value;
    }
    
    
    
    private function _getFormHtml($action, $method, $formFields) {
        $action = Tools::htmlentitiesUTF8($action);
        $html = '';
        
        $formElementsHtml = '';
        foreach ($formFields as $fieldName => $fieldData) {
            $methodName = '_get_' . $fieldData['type'] . '_html';
            $fieldPropertyName = '_' . $fieldName;
            $value = Tools::getValue(strtoupper($fieldName), $this->getFormConfigData(strtoupper($fieldName)));
            
            //multi store support, inject global values, when substore has been selected
            if ($this->_id_store || $this->_id_store_group) {
                $fieldData['global_value'] = $this->getGlobalConfigData(strtoupper($fieldName));
            }
            
            if (method_exists($this, $methodName)) {
                $formElementsHtml .= $this->$methodName(strtoupper($fieldName), $fieldData, $value);
            } else {
                $formElementsHtml .= $this->_get_text_html(strtoupper($fieldName), $fieldData, $value);
            }
        }
        
        
        $html .= <<<HTML
   <form action="{$action}" method="{$method}">
       <fieldset>
           <legend><img src="../img/admin/contact.gif" alt="" />{$this->l('Configuration details')}</legend>
               <table id="form">
               {$formElementsHtml}
                   <tr>
                       <td colspan="2"><input class="button" name="btnSubmit" value="{$this->l('Update settings')}" type="submit" /></td>
                   </tr>
               </table>
       </fieldset>
   </form>
HTML;
                       return $html;
    }
    
    
    public function getContent() {
        $this->_html = '<h2>' . $this->displayName . '</h2>';

        if (!empty($_POST)) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_displayHeader();
        $this->_displayForm();

        return $this->_html;
    }
    
    
    public function execPayment($cart) {
        if (!$this->active) {
            return;
        }

        global $cookie, $smarty;
        $curr = new Currency($cart->id_currency);
        $smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cookie->id_currency,
            'currencies' => $this->getCurrency(),
            'total' => number_format($cart->getOrderTotal(true, 3) / $curr->conversion_rate, 2, '.', ''),
            'isoCode' => Language::getIsoById(intval($cookie->id_lang)),
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        if ($this->ver == "1.6") {
            return $this->display(__FILE__, 'payment_exec.tpl');
        }
        return $this->display(__FILE__, 'payment_execution.tpl');
    }
    
    
    public function hookDisplayHeader($params) {
        if ($this->ver != '1.5') {
            $this->context->controller->addCSS($this->_path . $this->name . '.css', 'all');
        }
    }
    
    /**
     * <p>For adding CSS,JS scripts in backoffice</p>
     */
    public function hookActionAdminControllerSetMedia() {
        $this->context->controller->addJS($this->_path . 'js/' . $this->name . '.js');
    }
    
    
    
    public function hookPayment($params) {
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        global $smarty;

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'title' => $this->_title,
            'title' => htmlspecialchars($this->l($this->_title)),
            'description' => htmlspecialchars($this->l($this->_description)),
        ));
        if ($this->ver == '1.5') {
            return $this->display(__FILE__, 'payment_15.tpl');
        } else {
            return $this->display(__FILE__, 'payment.tpl');
        }
    }
    
    public function getDescription() {
        return $this->_description;
    }

    public function hookPaymentReturn($params) {
        if (!$this->active) {
            return;
        }

        global $smarty;
        $state = $params['objOrder']->getCurrentState();
        if ($state == _PS_OS_BANKWIRE_ OR $state == _PS_OS_OUTOFSTOCK_) {
            $smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
                'bankwireDetails' => nl2br2($this->details),
                'bankwireAddress' => nl2br2($this->address),
                'bankwireOwner' => $this->owner,
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
                $this->smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }
    public function startPayment($totalSum, $orderNr, $currency) {
        $paymentMessage = array(
            'shopId' => $this->_shop_id,
            'paymentId' => $orderNr,
            'amount' => number_format(round($totalSum, 2), 2, '.', ''),
        );
        $paymentMessage['signature'] = $this->_getStartSignature($paymentMessage, $this->_api_secret);


        $macFields = Array(
            'json' => json_encode($paymentMessage),
            'locale' => $this->_getPreferredLocale(),
        );

        return array('destination' => $this->_destination_url, 'params' => $macFields, 'method' => 'post');
    }

    public function validatePayment($params) {

        $macFields = false;
        $result = array(
            'data' => '',
            'amount' => 0,
            'status' => 'failed',
            'auto' => false,
        );
        foreach ((array) $params as $f => $v) {
            if ($f == 'json') {
                $macFields = $v;
            }
        }
        if (!$macFields) {
            return $result;
        }
        $paymentMessage = @json_decode($macFields, true);
        if (!$paymentMessage) {
            $paymentMessage = @json_decode(stripslashes($macFields), true);
        }
        if (!$paymentMessage) {
            $paymentMessage = @json_decode(htmlspecialchars_decode($macFields), true);
        }
        if (!$paymentMessage || !isset($paymentMessage['signature']) || !$paymentMessage['signature']) {
            return $result;
        }
        $sentSignature = $paymentMessage['signature'];


        if (isset($paymentMessage['shopId'])) {
            $paymentFailure = $paymentMessage['shopId'] != $this->_shop_id;
        } else {
            $paymentFailure = false;
        }
        if ($this->_getReturnSignature($paymentMessage, $this->_api_secret) != $sentSignature || $paymentFailure) {
            return $result;
        } else {
            if ($paymentMessage['status'] == 'RECEIVED') {
                $result['status'] = 'received';
                $result['data'] = $paymentMessage['paymentId'];
                $result['amount'] = $paymentMessage['amount'];
            } else if ($paymentMessage['status'] == 'PAID') {
                $result['status'] = 'success';
                $result['data'] = $paymentMessage['paymentId'];
                $result['amount'] = $paymentMessage['amount'];
                if (isset($paymentMessage['auto']) && $paymentMessage['auto']) {
                    $result['auto'] = true;
                }
            } else if ($paymentMessage['status'] == 'CANCELLED') {
                $result['status'] = 'cancelled';
                $result['data'] = $paymentMessage['paymentId'];
            }
            return $result;
        }
    }

    private function _getStartSignature($paymentMessage, $apiSecret) {
        $variableOrder = array(
            'shopId',
            'paymentId',
            'amount',
        );
        $stringToHash = '';
        foreach ($variableOrder as $messagePart) {
            $stringToHash .= $paymentMessage[$messagePart];
        }
        return strtoupper(hash('sha512', $stringToHash . $apiSecret));
    }

    private function _getReturnSignature($paymentMessage, $apiSecret) {
        $variableOrder = array(
            'paymentId',
            'amount',
            'status',
        );
        $stringToHash = '';
        foreach ($variableOrder as $messagePart) {
            $stringToHash .= $paymentMessage[$messagePart];
        }
        return strtoupper(hash('sha512', $stringToHash . $apiSecret));
    }

    private function _get_text_html($fieldName, $fieldData, $value = false) {
        $label = $fieldData['title'];
        $description = isset($fieldData['description'])?$fieldData['description']:'';
        $custom_attributes = isset($fieldData['custom_attributes']) && is_array($fieldData['custom_attributes'])?$fieldData['custom_attributes']:array();
        $styles = isset($fieldData['css'])?$fieldData['css']:'';
        if (!$value && isset($fieldData['default']) && $fieldData['default']) {
            $value = $fieldData['default'];
        }
        $finalCustomAttributes = array();
        foreach ($custom_attributes as $attributeKey => $attributeValue) {
            $finalCustomAttributes[] = $attributeKey.'="'.  htmlspecialchars($attributeValue).'"';
        }
        $_input = '<input type="text" name="'.$fieldName.'" style="'.$styles.'" value="'.htmlspecialchars($value).'" '.implode(' ', $finalCustomAttributes).'/>';
        $_input .= $this->_getGlobalValueCheckbox($fieldName, $fieldData, $value);
        if ($description) {
            $_input .= '<p>'.htmlspecialchars($description).'</p>';
        }
        return str_replace(array('${LABEL}', '${INPUT}'), array($label, $_input), $this->_html_template);
        
    }
    
    private function _get_textarea_html($fieldName, $fieldData, $value = false) {
        $label = $fieldData['title'];
        $description = isset($fieldData['description'])?$fieldData['description']:'';
        $custom_attributes = isset($fieldData['custom_attributes']) && is_array($fieldData['custom_attributes'])?$fieldData['custom_attributes']:array();
        $styles = isset($fieldData['css'])?$fieldData['css']:'';
        if (!$value && isset($fieldData['default']) && $fieldData['default']) {
            $value = $fieldData['default'];
        }
        $finalCustomAttributes = array();
        foreach ($custom_attributes as $attributeKey => $attributeValue) {
            $finalCustomAttributes[] = $attributeKey.'="'.  htmlspecialchars($attributeValue).'"';
        }
        $_input = '<textarea name="'.$fieldName.'" style="'.$styles.'" '.implode(' ', $finalCustomAttributes).'>'.htmlspecialchars($value).'</textarea>';
        $_input .= $this->_getGlobalValueCheckbox($fieldName, $fieldData, $value);
        if ($description) {
            $_input .= '<p>'.htmlspecialchars($description).'</p>';
        }
        return str_replace(array('${LABEL}', '${INPUT}'), array($label, $_input), $this->_html_template);
        
    }
    
    private function _get_select_html($fieldName, $fieldData, $value = false) {
        $label = $fieldData['title'];
        $description = isset($fieldData['description'])?$fieldData['description']:'';
        $custom_attributes = isset($fieldData['custom_attributes']) && is_array($fieldData['custom_attributes'])?$fieldData['custom_attributes']:array();
        $styles = isset($fieldData['css'])?$fieldData['css']:'';
        $options = isset($fieldData['options']) && is_array($fieldData['options'])?$fieldData['options']:array();
        if (!$value && isset($fieldData['default']) && $fieldData['default']) {
            $value = $fieldData['default'];
        }
        $finalCustomAttributes = array();
        foreach ($custom_attributes as $attributeKey => $attributeValue) {
            $finalCustomAttributes[] = $attributeKey.'="'.  htmlspecialchars($attributeValue).'"';
        }
        $_input = '<select name="'.$fieldName.'" style="'.$styles.'" value="'.htmlspecialchars($value).'" '.implode(' ', $finalCustomAttributes).'>';
        foreach ($options as $optionKey => $optionValue) {
            $_input .= '<option value="'.  htmlspecialchars($optionKey).'" ';
            if ($optionKey == $value) {
                $_input .= ' selected="selected"';
            }
            $_input .= '>';
            $_input .= htmlspecialchars($optionValue);
            $_input .= '</option>';
        }
        $_input .= '</select>';
        $_input .= $this->_getGlobalValueCheckbox($fieldName, $fieldData, $value);
        if ($description) {
            $_input .= '<p>'.htmlspecialchars($description).'</p>';
        }
        return str_replace(array('${LABEL}', '${INPUT}'), array($label, $_input), $this->_html_template);
        
    }
    
    
    private function _get_html_html($fieldName, $fieldData, $value = false) {
        $label = $fieldData['title'];
        $description = isset($fieldData['description'])?$fieldData['description']:'';
        $custom_attributes = isset($fieldData['custom_attributes']) && is_array($fieldData['custom_attributes'])?$fieldData['custom_attributes']:array();
        $styles = isset($fieldData['css'])?$fieldData['css']:'';
        $options = isset($fieldData['options']) && is_array($fieldData['options'])?$fieldData['options']:array();
        if (!$value && isset($fieldData['default']) && $fieldData['default']) {
            $value = $fieldData['default'];
        }
        $finalCustomAttributes = array();
        foreach ($custom_attributes as $attributeKey => $attributeValue) {
            $finalCustomAttributes[] = $attributeKey.'="'.  htmlspecialchars($attributeValue).'"';
        }
        $_input = $options['html'];
        if ($description) {
            $_input .= '<p>'.htmlspecialchars($description).'</p>';
        }
        return str_replace(array('${LABEL}', '${INPUT}'), array($label, $_input), $this->_html_template);
        
    }
    
    
    
    
    private function _get_stores_html($fieldName, $fieldData, $value = false) {
        $label = $fieldData['title'];
        $description = isset($fieldData['description'])?$fieldData['description']:'';
        $custom_attributes = isset($fieldData['custom_attributes']) && is_array($fieldData['custom_attributes'])?$fieldData['custom_attributes']:array();
        $styles = isset($fieldData['css'])?$fieldData['css']:'';
//        $options = isset($fieldData['options']) && is_array($fieldData['options'])?$fieldData['options']:array();
        $options = $this->_getStoreScopes($value);
        if (!$value && isset($fieldData['default']) && $fieldData['default']) {
            $value = $fieldData['default'];
        }
        $finalCustomAttributes = array();
        foreach ($custom_attributes as $attributeKey => $attributeValue) {
            $finalCustomAttributes[] = $attributeKey.'="'.  htmlspecialchars($attributeValue).'"';
        }
        $value = htmlspecialchars($this->_addUrlGetParams($_SERVER['REQUEST_URI'], array('id_store' => Tools::getValue('id_store', 0), 'id_store_group' => Tools::getValue('id_store_group', 0))));
        
        
        //onchange switchStore to selectedArea
        //by URL?
        $js = 'var a = confirm("Are you sure? All unsaved data will be lost"); if (a) { window.location.href = jQuery("<div />").html(jQuery(this).val()).text();} return a;';
        $_input = '<select onchange="'. htmlspecialchars(($js)).'" name="'.$fieldName.'" style="'.$styles.'" value="'.htmlspecialchars($value).'" '.implode(' ', $finalCustomAttributes).'>';
        
        
        
        
        
        
        foreach ($options as $optionKey => $optionValue) {
            $_input .= '<option value="'.  htmlspecialchars($optionKey).'" ';
            if ($optionKey == $value) {
                $_input .= ' selected="selected"';
            }
            $_input .= '>';
            $_input .= ($optionValue);
            $_input .= '</option>';
        }
        $_input .= '</select>';
        if ($description) {
            $_input .= '<p>'.htmlspecialchars($description).'</p>';
        }
        return str_replace(array('${LABEL}', '${INPUT}'), array($label, $_input), $this->_html_template);
        
    }
    
    protected function _getGlobalValueCheckbox($fieldName, $fieldData, $value) {
        if (!isset($fieldData['global_value'])) {
            return '';
        }
        $globalValue = $fieldData['global_value'];
        
        $input = '<span class="global_value"><input id="' . $fieldName. '_global" class="global_value" type="checkbox" name="" value="'. htmlspecialchars($globalValue).'"';
        if ($value == $globalValue) {
            $input .= ' checked="checked"';
        }
        $input .= '/>'.$this->l('Use global').'</span>';
        return <<<HTML
        {$input}
        <script type="text/javascript">
//        <![CDATA[
        jQuery("#{$fieldName}_global").maksekeskus_storescope();
//            ]]>
        </script>
HTML;
        
    }
    
    
    
    /**
     * 
     * @return array
     */
    protected function _getStoreScopes($selected = false) {
        $data = Shop::getTree();
        $options = array();


        $url = $_SERVER['REQUEST_URI'];
        $options[$this->_addUrlGetParams($url, array('id_store' => '0', 'id_store_group' => '0'))] = $this->l('Default configuration scope');

        foreach ($data as $storeGroup) {
            $storeGroupUri = htmlspecialchars($this->_addUrlGetParams($url, array('id_store' => '0', 'id_store_group' => $storeGroup['id'])));
            $options[$storeGroupUri] = sprintf(("&nbsp;&nbsp;%s"), $storeGroup['name']);
            foreach ($storeGroup['shops'] as $store) {
                $storeUri = htmlspecialchars($this->_addUrlGetParams($url, array('id_store' => $store['id_shop'], 'id_store_group' => $storeGroup['id'])));
//                      alternate option where group_id is marked as unindetified
//                $storeUri = htmlspecialchars($this->_addUrlGetParams($url, array('id_store' => $store['id_shop'], 'id_store_group' => '0')));
                $options[$storeUri] = sprintf(("&nbsp;&nbsp;&nbsp;&nbsp;%s"), $store['name']);

            }
        }
        return $options;
    }

    /**
     * <p>Adds GET parameters to end of current URL</p>
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function _addUrlGetParams($url, array $params) {
        $parsedUrl = parse_url($url);
        $query = $parsedUrl['query'];
        if (!count($params)) {
            return $url;
        }
        $append = array();
        if ($query) {
            parse_str($query, $append);
        }
        foreach ($params as $key => $param) {
            $append[$key] = $param;
        }
        $finalQuery = '';
        foreach ($append as $key => $value) {
            $finalQuery .= '&'.$key.'='.$value;
        }
        return $parsedUrl['path'].'?'.substr($finalQuery, 1);
    }

    private function _initFormFields() {
        if (count($this->form_fields)) {
            return $this->form_fields;
        }
        $returnUri = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/' . $this->name . '/validatePayment.php';
        
        //alternate return URL depending on selected store
        if ((int) Tools::getValue('id_store', 0)) {
            $shop = new Shop((int) Tools::getValue('id_store', 0));
            $urls = $shop->getUrls();
            if (isset($urls[0])) {
                $returnUri = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $urls[0]['domain_ssl'] . $urls[0]['physical_uri'] . 'modules/' . $this->name . '/validatePayment.php';
            }
        }


        $this->form_fields = array(
            'title' => array(
                'title' => $this->l('Title'),
                'type' => 'text',
                'description' => $this->l('This controls the title which the user sees during checkout.'),
                'default' => $this->l('Tasun pangalingi või krediitkaardiga'),
                'css' => 'width: 300px;',
            ),
            'description' => array(
                'title' => $this->l('Description'),
                'type' => 'textarea',
                'description' => $this->l('This controls the description which the user sees during checkout.'),
                'default' => '',
                'css' => 'width: 300px;',
            ),
            'destination_url' => array(
                'title' => $this->l('Destination URL'),
                'type' => 'text',
                'description' => $this->l('Here goes URL, where user is redirected to start the payment'),
                'default' => 'https://payment.maksekeskus.ee/pay/1/signed.html',
                'css' => 'width: 300px;',
            ),
            'shop_id' => array(
                'title' => $this->l('Shop ID'),
                'type' => 'text',
                'description' => sprintf($this->l('Maksekeskus provides you with %s.'), $this->l('Shop ID')),
                'default' => '',
                'css' => 'width: 300px;',
            ),
            'api_secret' => array(
                'title' => $this->l('API secret'),
                'type' => 'text',
                'description' => sprintf($this->l('Maksekeskus provides you with %s.'), $this->l('API secret')),
                'default' => '',
                'css' => 'width: 300px;',
            ),
            'locale' => array(
                'title' => $this->l('Preferred locale'),
                'type' => 'text',
                'description' => $this->l('RFC-2616 format locale. Like et,en,ru'),
                'default' => 'et',
                'css' => 'width: 300px;',
            ),
            'return' => array(
                'title' => $this->l('Return URL'),
                'type' => 'text',
                'description' => $this->l('Enter this URL to Maksekeskus database'),
                'default' => $returnUri,
                'custom_attributes' => array(
                    'readonly' => 'readonly',
                    'onfocus' => 'jQuery(this).select();'
                ),
                'css' => 'width: 300px;',
            ),
        );
        return $this->form_fields;
    }

    private function _initConfig($config) {
        foreach ($this->_config_array as $configKey) {
            if (isset($config[$this->_config_prefix . $configKey])) {
                $classProperty = '_' . strtolower($configKey);
                $this->$classProperty = $config[$this->_config_prefix . $configKey];
            }
        }
    }

    private function _getConfigArray($prefix, $array) {
        $result = array();
        foreach ($array as $value) {
            $result[] = $prefix.$value;
        }
        return $result;
    }

    protected function _getPreferredLocale() {
        $defaultLocale = 'et';
        $locale = $this->_locale;
        if ($locale) {
            $localeParts = explode('_', $locale);
            if (strlen($localeParts[0]) == 2) {
                return strtolower($localeParts[0]);
            } else {
                return $defaultLocale;
            }
        }
        return $defaultLocale;
    }

    public function checkCurrency($cart) {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
    
	public function addCheckboxCurrencyRestrictionsForModule(array $shops = array())
	{
		if (!$shops)
			$shops = Shop::getShops(true, null, true);

		foreach ($shops as $s)
		{
			if (!Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)
					SELECT '.(int)$this->id.', "'.(int)$s.'", `id_currency` FROM `'._DB_PREFIX_.'currency` WHERE deleted = 0 and iso_code = \'EUR\''))
				return false;
		}
		return true;
	}
        
	/**
	 * Add checkbox country restrictions for a new module
	 * @param integer id_module
	 * @param array $shops
	 */
	public function addCheckboxCountryRestrictionsForModule(array $shops = array())
	{
		$countries = Country::getCountries((int)Context::getContext()->language->id, true); //get only active country
		$country_ids = array();
		foreach ($countries as $country) {
                    if ($country['iso_code'] == 'EE') {
			$country_ids[] = $country['id_country'];
                    }
                }
		return Country::addModuleRestrictions($shops, $countries, array(array('id_module' => (int)$this->id)));
	}
        
    public function upgrade_module_0_2() {
        return $this->registerHook('displayHeader') && $this->registerHook('actionAdminControllerSetMedia');
    }
    

}

