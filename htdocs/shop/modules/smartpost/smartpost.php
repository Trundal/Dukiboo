<?php

/**
  Käesoleva loomingu autoriõigused kuuluvad Matis Halmannile ja Aktsiamaailm OÜ-le
  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents

 */
class Smartpost extends Module {

    const URL = 'http://www.smartpost.ee/places.json';
    const CONST_PREFIX = 'SMARTPOST_';
    private $_html = '';
    private $_postErrors = array();
    public $text;
    public $sort;
    public $short;
    public $fee_s;
    public $fee_m;
    public $fee_l;
    public $fee_xl;
    public $fee_none = 0;
    //shipping outside estonia
    public $free;
    public $limitStart;
    public $setDefault;
    public $tax;
    //free from certain client groups
    public $selectedClientGroups;
    //free from certain sum in cart
    public $allowfree;
    public $freeFromSum;
    //how to handle fees, per order or per item
    public $boxIteratorUnit;
    //just in case
    public $dataSendExecutor;
    
    private static $productSizes;
    public $group_width;
    public $office_width;
    public $disable_group_titles;
    public $disable_first;
    public $updateInterval;

    protected static $_helperModuleInstance;
    
    /**
     * <p>Evaluates to true, if current shipment method has already been rendered</p>
     * @var bool
     */
    protected $_carrierDisplayed = false;
    
    

    public function __construct() {
        $this->name = 'smartpost';
        $this->tab = 'shipping_logistics';
        $this->version = '0.8';
        $this->dependencies[] = 'eabi_postoffice';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');
        $this->init();
        parent::__construct();
        $this->displayName = $this->l('Smartposti moodul');
        $this->description = $this->l('moodsaim, mugavaim ja soodsaim pakkide saatmine ja kättesaamine.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        if (!isset($this->text) or !isset($this->fee_s))
            $this->warning = $this->l('Details must be configured in order to use this module correctly');
        if (file_exists(_PS_MODULE_DIR_ . $this->name. '/datasend-executor.php')) {
            require_once(_PS_MODULE_DIR_ . $this->name . '/datasend-executor.php');
            $executorClass = $this->name . '_data_send_executor';
            $this->dataSendExecutor = new $executorClass($this);
        }
    }
    
    public function getConfigData($param) {
        return Configuration::get(self::CONST_PREFIX . $param);
    }
    public function getGroupTitle($group) {
        return htmlspecialchars($group['group_name']);
    }

    public function getTerminalTitle($terminal) {
        if ($this->short == 'yes') {
            return htmlspecialchars($terminal['name']);
        }
        return htmlspecialchars($terminal['name'] . ' ' . $terminal['description']);
    }

    public function getAdminTerminalTitle($terminal) {
        if ($this->short == 'yes') {
            return htmlspecialchars($terminal['group_name'] . ' - ' . $terminal['name']);
        }
        return htmlspecialchars($terminal['group_name'] . ' - ' . $terminal['name'] . ' ' . $terminal['description']);
    }
    
    public function getGroupSort($city) {
        return 0;
    }

    private function init() {
        $config = Configuration::getMultiple(array(
                    self::CONST_PREFIX.'TEXT',
                    self::CONST_PREFIX.'SORT',
                    self::CONST_PREFIX.'SHORT',
                    self::CONST_PREFIX.'FEE_S',
                    self::CONST_PREFIX.'FEE_M',
                    self::CONST_PREFIX.'FEE_L',
                    self::CONST_PREFIX.'FEE_XL',
                    self::CONST_PREFIX.'FREE',
                    self::CONST_PREFIX.'LIMIT',
                    self::CONST_PREFIX.'DEFAULT',
                    self::CONST_PREFIX.'TAX',
                    self::CONST_PREFIX.'ALLOWFREE',
                    self::CONST_PREFIX.'CLIENTS',
                    self::CONST_PREFIX.'FREE_FROM_SUM',
                    self::CONST_PREFIX.'BOX_UNIT',
                    self::CONST_PREFIX.'GR_WIDTH',
                    self::CONST_PREFIX.'OF_WIDTH',
                    self::CONST_PREFIX.'DIS_GR_TITLE',
                    self::CONST_PREFIX.'UPD_INTERVAL',
                    self::CONST_PREFIX.'DIS_FIRST',
                ));
        if (isset($config[self::CONST_PREFIX.'TEXT']))
            $this->text = $config[self::CONST_PREFIX.'TEXT'];
        if (isset($config[self::CONST_PREFIX.'SORT']))
            $this->sort = $config[self::CONST_PREFIX.'SORT'];
        if (isset($config[self::CONST_PREFIX.'SHORT']))
            $this->short = $config[self::CONST_PREFIX.'SHORT'];
        if (isset($config[self::CONST_PREFIX.'FEE_S']))
            $this->fee_s = $config[self::CONST_PREFIX.'FEE_S'];
        if (isset($config[self::CONST_PREFIX.'FEE_M']))
            $this->fee_m = $config[self::CONST_PREFIX.'FEE_M'];
        if (isset($config[self::CONST_PREFIX.'FEE_L']))
            $this->fee_l = $config[self::CONST_PREFIX.'FEE_L'];
        if (isset($config[self::CONST_PREFIX.'FEE_XL']))
            $this->fee_xl = $config[self::CONST_PREFIX.'FEE_XL'];
        if (isset($config[self::CONST_PREFIX.'FREE']))
            $this->free = $config[self::CONST_PREFIX.'FREE'];
        if (isset($config[self::CONST_PREFIX.'LIMIT']))
            $this->limitStart = $config[self::CONST_PREFIX.'LIMIT'];
        if (isset($config[self::CONST_PREFIX.'DEFAULT']))
            $this->setDefault = $config[self::CONST_PREFIX.'DEFAULT'];
        if (isset($config[self::CONST_PREFIX.'TAX']))
            $this->tax = $config[self::CONST_PREFIX.'TAX'];
        if (isset($config[self::CONST_PREFIX.'CLIENTS']))
            $this->selectedClientGroups = $config[self::CONST_PREFIX.'CLIENTS'];
        if (isset($config[self::CONST_PREFIX.'ALLOWFREE']))
            $this->allowfree = $config[self::CONST_PREFIX.'ALLOWFREE'];
        if (isset($config[self::CONST_PREFIX.'FREE_FROM_SUM']))
            $this->freeFromSum = $config[self::CONST_PREFIX.'FREE_FROM_SUM'];
        if (isset($config[self::CONST_PREFIX.'BOX_UNIT']))
            $this->boxIteratorUnit = $config[self::CONST_PREFIX.'BOX_UNIT'];

        /*
         *                     self::CONST_PREFIX.'GR_WIDTH',
          self::CONST_PREFIX.'OF_WIDTH',
          self::CONST_PREFIX.'DIS_GR_TITLE',

         */
        if (isset($config[self::CONST_PREFIX.'GR_WIDTH'])) {
            $this->group_width = $config[self::CONST_PREFIX.'GR_WIDTH'];
        }
        if (isset($config[self::CONST_PREFIX.'OF_WIDTH'])) {
            $this->office_width = $config[self::CONST_PREFIX.'OF_WIDTH'];
        }
        if (isset($config[self::CONST_PREFIX.'DIS_GR_TITLE'])) {
            $this->disable_group_titles = $config[self::CONST_PREFIX.'DIS_GR_TITLE'];
        }
        if (isset($config[self::CONST_PREFIX.'UPD_INTERVAL'])) {
            $this->updateInterval = $config[self::CONST_PREFIX.'UPD_INTERVAL'];
        } else {
            $this->updateInterval = 1440;
        }
        if (!$this->updateInterval) {
            $this->updateInterval = 1440;
        }
        if (isset($config[self::CONST_PREFIX.'DIS_FIRST'])) {
            $this->disable_first = $config[self::CONST_PREFIX.'DIS_FIRST'];
        }

        if ($this->dataSendExecutor != null) {
            $this->dataSendExecutor->init();
        }
    }

    public function install() {
        if (!parent::install() or !$this->registerHook('extraCarrier')
                or !$this->_getHelperModule()->addCarrierModule($this->name, get_class($this)) or !$this->registerHook('paymentConfirm') || !$this->registerHook('displayFooter'))
            return false;
        return true;
    }

    public function uninstall() {
        if (!Configuration::deleteByName(self::CONST_PREFIX.'TEXT') or !Configuration::
                deleteByName(self::CONST_PREFIX.'SORT') or !Configuration::deleteByName(self::CONST_PREFIX.'SHORT') or
                !Configuration::deleteByName(self::CONST_PREFIX.'FEE_S') or !Configuration::deleteByName
                        (self::CONST_PREFIX.'FEE_M') or !Configuration::deleteByName(self::CONST_PREFIX.'FEE_L') or !
                Configuration::deleteByName(self::CONST_PREFIX.'FEE_XL') or !Configuration::deleteByName
                        (self::CONST_PREFIX.'FREE') or !Configuration::deleteByName(self::CONST_PREFIX.'LIMIT') or !
                Configuration::deleteByName(self::CONST_PREFIX.'DEFAULT') or !Configuration::
                deleteByName(self::CONST_PREFIX.'TAX') or !Configuration::deleteByName(self::CONST_PREFIX.'ALLOWFREE') or
                !Configuration::deleteByName(self::CONST_PREFIX.'CLIENTS') or !Configuration::
                deleteByName(self::CONST_PREFIX.'FREE_FROM_SUM') or !Configuration::deleteByName(self::CONST_PREFIX.'BOX_UNIT')
                || !Configuration::deleteByName(self::CONST_PREFIX.'GR_WIDTH')
                || !Configuration::deleteByName(self::CONST_PREFIX.'OF_WIDTH')
                || !Configuration::deleteByName(self::CONST_PREFIX.'DIS_GR_TITLE')
                || !Configuration::deleteByName(self::CONST_PREFIX.'UPD_INTERVAL')
                || !Configuration::deleteByName(self::CONST_PREFIX.'LST_UPD')
                || !Configuration::deleteByName(self::CONST_PREFIX.'DIS_FIRST')
        )
            return false;
        if (!$this->unregisterHook('extraCarrier') || !$this->unregisterHook('paymentConfirm') || !$this->unregisterHook('displayFooter'))
            return false;
        if (!$this->_getHelperModule()->removeCarrierModule($this->name)) {
            return false;
        }

        if ($this->dataSendExecutor != null) {
            $this->dataSendExecutor->uninstall();
        }
        if (!parent::uninstall())
            return false;
        //remove the default carrier
        if ($this->setDefault == 'yes') {
            Configuration::updateValue('PS_CARRIER_DEFAULT', 1);
        }
        return true;
    }

    
    
    /**
     * <p>For adding HOOK_EXTRACARRIER callout, when original callout did not occur when it had to.</p>
     * <p>PrestaShop does not call extracarrier when for example address is not entered yet</p>
     * @return string
     */
    public function hookDisplayFooter() {
        $className = 'OrderOpcController';
        $php_self = 'order-opc';
        if ($this->context->controller instanceof $className && $this->context->controller->php_self == $php_self) {
            if (!$this->_carrierDisplayed) {
                return $this->hookExtraCarrier(array('cart' => $this->context->cart));
            }
        }
    }

    /**
     * <p>We need this to override addressId, so always one terminal would be available</p>
     * @param type $code
     * @param type $groupId
     * @param type $officeId
     * @param null $addressId
     */
    public function __getPostOffices($code, &$groupId = null, &$officeId = null, &$addressId = null) {
        $addressId = null;
    }
    
    private function _postValidation() {
        if (isset($_POST['btnSubmit'])) {
            if (empty($_POST['text']))
                $this->_postErrors[] = $this->l('title is required.');
            if (empty($_POST['short']))
                $this->_postErrors[] = $this->l('Show short box names is required.');
            if (empty($_POST['fee_s']))
                $this->_postErrors[] = $this->l('small box shipping fee is required.');
            if (empty($_POST['fee_m']))
                $this->_postErrors[] = $this->l('medium box shipping fee is required.');
            if (empty($_POST['fee_l']))
                $this->_postErrors[] = $this->l('large box shipping fee is required.');
            if (empty($_POST['fee_xl']))
                $this->_postErrors[] = $this->l('extra large box shipping fee is required.');
            if (empty($_POST['free']))
                $this->_postErrors[] = $this->l('Outside Estonia shipping setting is required.');
            if (!isset($_POST['tax']))
                $this->_postErrors[] = $this->l('Tax is required.');
            if (empty($_POST['allowfree']))
                $this->_postErrors[] = $this->l('Allow free is required.');
            if (!empty($_POST['allowfree']) && $_POST['allowfree'] == 'yes' && (empty($_POST['freeFromSum']) ||
                    !is_numeric(str_replace(',', '.', $_POST['freeFromSum']))))
                $this->_postErrors[] = $this->l('Free from Sum is required.');
            if (empty($_POST['boxIteratorUnit']) || !in_array($_POST['boxIteratorUnit'], array('order', 'item')))
                $this->_postErrors[] = $this->l('Calculate shipping fee is required.');
            if (empty($_POST['disableGroupTitles']) || !in_array($_POST['disableGroupTitles'], array('yes', 'no')))
                $this->_postErrors[] = $this->l('Disable Group Titles is required.');
            if (empty($_POST['disableFirst']) || !in_array($_POST['disableFirst'], array('yes', 'no')))
                $this->_postErrors[] = $this->l('Disable Groups menu.');
            if (!isset($_POST['officeWidth'])) {
                $this->_postErrors[] = $this->l('Missing required Post parameter.');
            }
            if (!isset($_POST['groupWidth'])) {
                $this->_postErrors[] = $this->l('Missing required Post parameter.');
            }
            if (!isset($_POST['updateInterval'])) {
                $this->_postErrors[] = $this->l('Missing required Post parameter.');
            }

            if ($this->dataSendExecutor != null) {
                $this->dataSendExecutor->_postValidation($this->_postErrors);
            }
        }
    }
    
    public function getOfficeList() {
        //implement this
        $body = file_get_contents(self::URL);
        $result = array();
        $body = json_decode($body, true);
        foreach ($body as $bodyElement) {
            $bodyCsv = (object)$bodyElement;
            //group name
            $countyParts = explode("/", $bodyCsv->group_name);
            if (!isset($countyParts[1])) {
                $county = $countyParts[0];
            } else {
                $county = $countyParts[1];
            }
            $result[] = array(
                            'place_id' => (int)$bodyCsv->place_id, 
                            'name' => $bodyCsv->name, 
                            'city' => $bodyCsv->city, 
                            'county' => $county, 
                            'description' => $bodyCsv->description, 
                            'country' => 'EE', 
                            'group_sort' => $bodyCsv->group_sort, );
        }
        if (count($result) == 0) {
            
            return false;
        }
        
        return $result;
    }

    private function _postProcess() {
        if (isset($_POST['btnSubmit'])) {
            Configuration::updateValue(self::CONST_PREFIX.'TEXT', $_POST['text']);
            Configuration::updateValue(self::CONST_PREFIX.'SHORT', $_POST['short']);
            Configuration::updateValue(self::CONST_PREFIX.'FEE_S', str_replace(',', '.', $_POST['fee_s']));
            Configuration::updateValue(self::CONST_PREFIX.'FEE_M', str_replace(',', '.', $_POST['fee_m']));
            Configuration::updateValue(self::CONST_PREFIX.'FEE_L', str_replace(',', '.', $_POST['fee_l']));
            Configuration::updateValue(self::CONST_PREFIX.'FEE_XL', str_replace(',', '.', $_POST['fee_xl']));
            Configuration::updateValue(self::CONST_PREFIX.'FREE', $_POST['free']);
            Configuration::updateValue(self::CONST_PREFIX.'LIMIT', $_POST['limitStart']);
            Configuration::updateValue(self::CONST_PREFIX.'DEFAULT', $_POST['setDefault']);
            Configuration::updateValue(self::CONST_PREFIX.'TAX', $_POST['tax']);
            Configuration::updateValue(self::CONST_PREFIX.'ALLOWFREE', $_POST['allowfree']);
            if (isset($_POST['selectedClientGroups']) && is_array($_POST['selectedClientGroups']) && count($_POST['selectedClientGroups'])) {
                Configuration::updateValue(self::CONST_PREFIX.'CLIENTS', implode(',', $_POST['selectedClientGroups']));
            } else {
                Configuration::updateValue(self::CONST_PREFIX.'CLIENTS', '');
            }
            Configuration::updateValue(self::CONST_PREFIX.'FREE_FROM_SUM', str_replace(',', '.', $_POST['freeFromSum']));
            Configuration::updateValue(self::CONST_PREFIX.'BOX_UNIT', $_POST['boxIteratorUnit']);
            Configuration::updateValue(self::CONST_PREFIX.'UPD_INTERVAL', $_POST['updateInterval']);
            Configuration::updateValue(self::CONST_PREFIX.'GR_WIDTH', $_POST['groupWidth']);
            Configuration::updateValue(self::CONST_PREFIX.'OF_WIDTH', $_POST['officeWidth']);
            Configuration::updateValue(self::CONST_PREFIX.'DIS_GR_TITLE', $_POST['disableGroupTitles']);
            Configuration::updateValue(self::CONST_PREFIX.'DIS_FIRST', $_POST['disableFirst']);

            /*
             *                     self::CONST_PREFIX.'GR_WIDTH',
              self::CONST_PREFIX.'OF_WIDTH',
              self::CONST_PREFIX.'DIS_GR_TITLE',

             */


            if ($this->dataSendExecutor != null) {
                $this->dataSendExecutor->_postProcess();
            }


            //call the constructor
            $lastDefault = $this->setDefault;
            
            $lastFree = $this->allowfree;
            $this->init();
            $this->_getHelperModule()->setTaxGroup($this->name, $this->tax);
            $this->_getHelperModule()->setDisplayName($this->name, $this->text);
            $this->_getHelperModule()->refresh($this->name, true);
            //$this->d($res);
            //set the default carrier
            if ($this->setDefault == 'yes') {
                $prestaCarrierValue = $this->_getHelperModule()->getCarrierFromCode($this->name);
                Configuration::updateValue('PS_CARRIER_DEFAULT', $prestaCarrierValue->id);
            }
            if ($lastDefault == 'yes' && $this->setDefault == 'no') {
                //put back the default carrier
                Configuration::updateValue('PS_CARRIER_DEFAULT', 1);
            }
            if ($lastFree == 'yes' && $this->allowfree == 'no') {
                //put back the default carrier
                $this->_html .= $this->_getHelperModule()->addSuccess($this->l('You chose to remove free shipping, please make sure that the default shipping range covers all Your shipping needs'));
            }
            if (strlen($this->selectedClientGroups)) {
                if (count($this->_getHelperModule()->getClientGroups()) == count(explode(',', $this->selectedClientGroups))) {
                    $this->_html .= $this->_getHelperModule()->addSuccess($this->l('All the clients in your shop are getting free shipping, because you have selected all client groups eligible for free shipping'));
                }
                
            }
            
            $this->_html .= $this->_getHelperModule()->addSuccess($this->l('Settings updated'));
        }
    }


    private function _displaySmartpost() {
        $this->_html .= '<img src="../modules/smartpost/smartpost.gif" style="float:left; margin-right:15px;"><b>' .
                $this->l('This module allows Your customer to choose Smartpost as shipping method.') .
                '</b><br /><br />
		' . $this->l('Client can choose the package box that he or she requires and also shipping fee can be altered.') .
                '<br />
		' . $this->l('Also free shipping is possible.') . '<br /><br /><br />';
    }


    private function _displayForm() {
        $yesno = array('no' => $this->l('no'), 'yes' => $this->l('yes'));
        $boxUnits = array('order' => $this->l('Per Order'), 'item' => $this->l('Per Item'));
        $qu = "SELECT p.id_tax_rules_group as id_tax, p.name FROM `" . _DB_PREFIX_ . "tax_rules_group` p WHERE p.active = 1";

        $res = Db::getInstance()->executeS($qu);
        $taxes = array('0' => $this->l('No Tax'));
        foreach ($res as $r) {
            $taxes[$r['id_tax']] = $r['name'];
        }

        $this->_html .= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />' . $this->l('Smartpost configuration details') .
                '</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">' . $this->l('Title displayed to the user, when choosing shipping method method') .
                '.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Text displayed to the user') .
                '</td><td><input type="text" name="text" value="' . htmlentities(Tools::getValue('text', $this->text), ENT_COMPAT, 'UTF-8') . '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Show short names of the parcel terminals') .
                '</td><td><select name="short" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('short', $this->short)) . '</select></td></tr>

					<tr><td width="130" style="height: 35px;">' . $this->l('Shipping fee for small box (12*36*60cm)') .
                '</td><td><input type="text" name="fee_s" value="' . htmlentities(Tools::getValue
                                ('fee_s', $this->fee_s), ENT_COMPAT, 'UTF-8') .
                '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Shipping fee for medium box (20*36*60cm)') .
                '</td><td><input type="text" name="fee_m" value="' . htmlentities(Tools::getValue
                                ('fee_m', $this->fee_m), ENT_COMPAT, 'UTF-8') .
                '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Shipping fee for large box (38*36*60cm)') .
                '</td><td><input type="text" name="fee_l" value="' . htmlentities(Tools::getValue
                                ('fee_l', $this->fee_l), ENT_COMPAT, 'UTF-8') .
                '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Shipping fee for extra large box (60*36*60cm)') .
                '</td><td><input type="text" name="fee_xl" value="' . htmlentities(Tools::
                        getValue('fee_xl', $this->fee_xl), ENT_COMPAT, 'UTF-8') .
                '" style="width: 300px;" /></td></tr>

					<tr><td width="130" style="height: 35px;">' . $this->l('Disable shipping outside Estonia') .
                '</td><td><select name="free" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('free', $this->free)) . '</select></td></tr>
					<tr><td width="130" style="height: 35px;">' . sprintf($this->l('Disable this module if products short description in bascket contains the HTML comment of %s'), htmlspecialchars('<!-- no smartpost -->'))  .
                '</td><td><select name="limitStart" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('limitStart', $this->limitStart)) .
                '</select></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Make this carrier default') .
                '</td><td><select name="setDefault" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('setDefault', $this->setDefault)) .
                '</select></td></tr>

					<tr><td width="130" style="height: 35px;">' . $this->l('Allow free shipping') .
                '</td><td><select name="allowfree" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('allowfree', $this->allowfree)) . '</select></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Free shipping from Sum') .
                '</td><td><input type="text" name="freeFromSum" value="' . htmlentities(Tools::
                        getValue('freeFromSum', $this->freeFromSum), ENT_COMPAT, 'UTF-8') .
                '" style="width: 300px;" /></td></tr>


					<tr><td width="130" style="height: 35px;">' . $this->l('Calculate shipping fee') .
                '</td><td><select name="boxIteratorUnit" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($boxUnits, Tools::getValue('boxIteratorUnit', $this->
                                boxIteratorUnit)) . '</select></td></tr>

					<tr><td width="130" style="height: 35px;">' . $this->l('Client groups who can get free shipping (hold down CTRL / CMD button to select multiple)') .
                '</td><td><select multiple="multiple" name="selectedClientGroups[]" style="width: 300px;">' .
                $this->_getHelperModule()->getMultiselectList($this->_getHelperModule()->getClientGroups(), Tools::getValue('selectedClientGroups', $this->selectedClientGroups)) . '</select></td></tr>
            <tr><td width="130" style="height: 35px;">' . $this->l('Tax Id') .
                '</td><td><select name="tax" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($taxes, Tools::getValue('tax', $this->tax)) . '</select></td></tr>
                
            <tr><td width="130" style="height: 35px;">' . $this->l('Use one dropdown menu instead of two when selecting parcel terminal') .
                '</td><td><select name="disableFirst" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('disableFirst', $this->disable_first)) . '</select></td></tr>
                
            <tr><td width="130" style="height: 35px;">' . $this->l('Disable Group Titles') .
                '</td><td><select name="disableGroupTitles" style="width: 300px;">' . $this->_getHelperModule()->getOptionList($yesno, Tools::getValue('disableGroupTitles', $this->disable_group_titles)) . '</select></td></tr>
                
                
					<tr><td width="130" style="height: 35px;">' . $this->l('First select menu width in pixels') .
                '</td><td><input type="text" name="groupWidth" value="' . htmlentities(Tools::getValue('groupWidth', $this->group_width), ENT_COMPAT, 'UTF-8') . '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Second select menu width in pixels') .
                '</td><td><input type="text" name="officeWidth" value="' . htmlentities(Tools::getValue('officeWidth', $this->office_width), ENT_COMPAT, 'UTF-8') . '" style="width: 300px;" /></td></tr>
					<tr><td width="130" style="height: 35px;">' . $this->l('Auto Update Interval in minutes, default 1440') .
                '</td><td><input type="text" name="updateInterval" value="' . htmlentities(Tools::getValue('updateInterval', $this->updateInterval), ENT_COMPAT, 'UTF-8') . '" style="width: 300px;" /></td></tr>
                '
        ;
        if ($this->dataSendExecutor != null) {
            $this->_html.=$this->dataSendExecutor->_displayForm();
        }
        $this->_html.='<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="' .
                $this->l('Update settings') . '" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
    }

    public function displayInfoByCart($cart_id) {
        $offices = $this->_getHelperModule()->getOfficesFromCart($cart_id);
        $terminals = array();
        foreach ($offices as $address_id => $office) {
            $terminals[] = $this->getAdminTerminalTitle($office);
        }
        $extraInfo = '';
        if ($this->dataSendExecutor != null) {
            $extraInfo = $this->dataSendExecutor->displayInfoByCart($cart_id);
        }
        return $this->l('Chosen parcel terminal:') . ' <b>' . implode(', ', $terminals) . '</b>'.$extraInfo;
    }

    public function getContent() {
        $this->_html = '<h2>' . $this->displayName . '</h2>';
        if (!empty($_POST)) {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
        } else
            $this->_html .= '<br />';
        $this->_displaySmartpost();
        $this->_displayForm();
        return $this->_html;
    }

    
    
    
    /**
     * 
     * @global type $smarty
     * @param type $params
     * @return type
     * 
     * Returns extra carrier HTML
     * Should return javascipt, which....
     * * Hides SmartPOST methods entirely, when not in the allowed list, or products are bad
     * * Return false should return the correcting HTML instead.....
     * * If parcel terminals found, then it should collect all the smartpost terminals
     *  and display them as one select (hide them all, inject select menu.)
     */
    public function hookExtraCarrier($params) {

        $shouldHide = false;
        if (!$this->active) {
            $shouldHide = true;
        }
        $cart = $params['cart'];
        $summ = $cart->getSummaryDetails();
        if ($this->free == 'yes') {
            $country = $summ['delivery']->country;
            if (stripos($country, 'estonia') === false && stripos($country, 'eesti') === false) {
                $shouldHide = true;
            }
        }
        if (!$summ['delivery']->country) {
            $shouldHide = true;
            if (!isset($params['address']) || !$params['address']) {
                $params['address'] = (object)array(
                    'id' => '0',
                );
            }
        }
        if ($this->limitStart == 'yes' && !$shouldHide) {
            $prods = $cart->getProducts();
            foreach ($prods as $prod) {
                if (stripos($prod['description_short'], '<!-- no smartpost -->') !== false) {
                    $shouldHide = true;
                    break;
                }
            }
        }
        if (!$shouldHide) {
            $cartSizes = $this->getProductSizes($cart);
            //make sure that basket does not contain products too large
            foreach ($cartSizes as $size) {
                if (!$this->isShippable($size)) {
                    $shouldHide = true;
                    break;
                }
            }

        }
        
        $error = '';
        //check the phone
        if ($summ['delivery']->phone == '' && $summ['delivery']->phone_mobile == '' && false) {
            $error .= '<br/><div class="error"><ol><li><a href="address.php?id_address=' . $summ['delivery']->
                    id . '">' . $this->l('Your phone number is required, please assign a phone number!') .
                    '</a></li></ol></div>';
        }
        
        
        $extraParams = array(
            'id_address_delivery' => $cart->id_address_delivery,
            'price' => $this->getOrderShippingCost($cart),
            'title' => $this->l($this->text),
            'logo' => __PS_BASE_URI__ . 'modules/' . $this->name . '/logo.gif',
            'id_address_invoice' => $cart->id_address_invoice,
            'error_message' => $error,
            'is_default' => false,
        );
        
        return $this->_getHelperModule()->displayExtraCarrier($this->name, $extraParams, $shouldHide);
    }
    
    
    

    private function getProductSizes($cartObject) {
        if (self::$productSizes == null) {
            $cartProducts = $cartObject->getProducts(false);
            self::$productSizes = array();
            foreach ($cartProducts as $product) {
                self::$productSizes[] = array(
                    'w' => $product['width'],
                    'h' => $product['height'],
                    'd' => $product['depth'],
                    'q' => $product['quantity'],
                );
            }
        }
        return self::$productSizes;
    }

    private function isShippable($productSize) {
        $size = array(
            $productSize['w'],
            $productSize['h'],
            $productSize['d']);
        sort($size);
        if ($size[0] > 36) {
            return false;
        }
        if (max($size) > 60)
            return false;
        return true;
    }

    //if $carrier->shipping_external
    //if $carrier->need_range
    public function getOrderShippingCost($cartObject, $shippingPrice = 0) {
        if ($cartObject->id_customer > 0) {
            //check the customer group
            $freeGroups = explode(',', $this->selectedClientGroups);
            if (count($freeGroups) > 0 && $this->selectedClientGroups != '') {
                $customerGroups = CustomerCore::getGroupsStatic($cartObject->id_customer);
                foreach ($customerGroups as $customerGroup) {
                    if (in_array($customerGroup, $freeGroups)) {
                        //free shipping if customer belongs to group
                        return 0;
                    }
                }
            }
        }
        $totalSum = $cartObject->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        //check if free shipping by total sum  is enabled
        if ($this->allowfree == 'yes' && $totalSum >= Tools::convertPrice($this->
                        freeFromSum, Currency::getCurrencyInstance((int) ($cartObject->id_currency)))) {
            return 0;
        }
        $prodSizes = $this->getProductSizes($cartObject);
        if (!count($prodSizes)) {
            return 0;
        }
        
        //calculate the shipping by boxes
        //at this point we have already made sure that the cart only contains producs which can be sent
        //using this shipping method
        $shippingCosts = array();
        foreach ($prodSizes as $prodSize) {
            $size = array(
                $prodSize['w'],
                $prodSize['h'],
                $prodSize['d']);
            sort($size);
            //swap the sizes
            if ($this->boxIteratorUnit == 'item') {
                if ($size[0] <= 12 && $size[1] <= 36) {
                    $shippingCosts[] = $this->fee_s * $prodSize['q'];
                } else if ($size[0] <= 20 && $size[1] <= 36) {
                    $shippingCosts[] = $this->fee_m * $prodSize['q'];
                } else if ($size[0] <= 36 && $size[1] <= 38) {
                    $shippingCosts[] = $this->fee_l * $prodSize['q'];
                } else if ($size[0] <= 36) {
                    $shippingCosts[] = $this->fee_xl * $prodSize['q'];
                }
            } else {
                if ($size[0] <= 12 && $size[1] <= 36) {
                    $shippingCosts[] = $this->fee_s;
                } else if ($size[0] <= 20 && $size[1] <= 36) {
                    $shippingCosts[] = $this->fee_m;
                } else if ($size[0] <= 36 && $size[1] <= 38) {
                    $shippingCosts[] = $this->fee_l;
                } else if ($size[0] <= 36) {
                    $shippingCosts[] = $this->fee_xl;
                }
            }
        }
        $shippingCost = 0;
        //iterator completed
        if ($this->boxIteratorUnit == 'item') {
            $shippingCost = array_sum($shippingCosts);
        } else {
            $shippingCost = max($shippingCosts);
        }
        $convertedPrice = Tools::convertPrice($shippingCost, Currency::getCurrencyInstance((int) ($cartObject->
                                id_currency)));




        return $convertedPrice;
    }

    //if $carrier->shipping_external
    //if !$carrier->need_range
    public function getOrderShippingCostExternal($cartObject) {
        return $this->getOrderShippingCost($cartObject, 0);
    }


    public function hookPaymentConfirm(&$params) {
        if ($this->dataSendExecutor != null) {
            $this->dataSendExecutor->hookpaymentConfirm($params);
        }
        return 'a';
    }

    public function ls($var) {
        return $this->l($var);
    }

    public function gp($var1, $var2) {
        $yesno = array('no' => $this->l('no'), 'yes' => $this->l('yes'));
        return $this->getOptionList($yesno, Tools::getValue($var1, $var2));
    }
    
    /**
     * 
     * @return Eabi_Postoffice
     */
    public function _getHelperModule() {
        if (is_null(self::$_helperModuleInstance)) {
            self::$_helperModuleInstance = Module::getInstanceByName('eabi_postoffice');
        }
        return self::$_helperModuleInstance;
    }
    
    public function getUpdateInterval() {
        $interval = $this->getConfigData('UPD_INTERVAL');
        if (!$interval) {
            return 1440;
        }
        return $interval;
    }

    public function setLastUpdated($lastUpdated) {
        Configuration::updateValue(self::CONST_PREFIX.'LST_UPD', $lastUpdated);
        return;
    }

    public function getLastUpdated() {
        return $this->getConfigData('LST_UPD');
    }

}
