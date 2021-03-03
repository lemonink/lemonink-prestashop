<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/vendor/autoload.php';

use PrestaShop\PrestaShop\Adapter\Entity\FileLogger;

class LemonInk extends Module
{
    protected $config_form = false;
    protected $logger = null;

    public function __construct()
    {
        $this->name = 'lemonink';
        $this->tab = 'others';
        $this->version = '0.1.0';
        $this->author = 'LemonInk';
        $this->need_instance = 0;
        $this->module_key = '35176e9a54aa1c6b1404e6b0961b287a';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('LemonInk Ebook Watermarking for PrestaShop');
        $this->description = $this->l(
            'Watermark ebooks in EPUB, MOBI and PDF in your PrestaShop store using the LemonInk service.'
        );

        $this->confirmUninstall = $this->l('Are you sure? Files set up to use LemonInk ' .
            'won\'t be available for purchase anymore.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        // $this->logger = new FileLogger();
        // $this->logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/lemonink.log');
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            Configuration::updateValue('LEMONINK_WATERMARKING_ORDER_STATE', 2) &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayOrderDetail');
    }

    public function uninstall()
    {
        Configuration::deleteByName('LEMONINK_API_KEY');
        Configuration::deleteByName('LEMONINK_WATERMARKING_ORDER_STATE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = null;

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitLemoninkModule')) == true) {
            $apiKey = (string)Tools::getValue('LEMONINK_API_KEY');
            $unlink = (string)Tools::getValue('LEMONINK_UNLINK');
            
            if ($unlink) {
                Configuration::updateValue('LEMONINK_API_KEY', null);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            } elseif ($apiKey && !empty($apiKey)) {
                Configuration::updateValue('LEMONINK_API_KEY', $apiKey);
            } else {
                $output .= $this->displayError($this->l('This API key is invalid.
                    Please make sure that you\'ve copied the correct value'));
            }
        }

        if (Configuration::get('LEMONINK_API_KEY')) {
            $output .= $this->displayConfirmation($this->l('Your store is linked with LemonInk'));
        }

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLemoninkModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array('LEMONINK_UNLINK' => 'unlink'),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $input = null;
        $submit = null;

        if (Configuration::get('LEMONINK_API_KEY', true)) {
            $input = array(
                'type' => 'hidden',
                'name' => 'LEMONINK_UNLINK',
                'value' => 'unlink'
            );
            $submit = array(
                'title' => $this->l('Unlink'),
            );
        } else {
            $input = array(
                'type' => 'password',
                'label' => $this->l('API Key'),
                'name' => 'LEMONINK_API_KEY',
                'desc' => $this->l('LemonInk API Key')
            );
            $submit = array(
                'title' => $this->l('Save'),
            );
        }
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    $input
                ),
                'submit' => $submit
            ),
        );
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayAdminProductsExtra(array $params)
    {
        $id_product = $params['id_product'];
        $productMaster = ProductMaster::loadByProductId($id_product);
        $this->context->smarty->assign(array(
            'master_id' => $productMaster->master_id
        ));
        return $this->display(__FILE__, 'views/templates/admin/product_form.tpl');
    }

    public function hookActionProductUpdate()
    {
        $id_product = Tools::getValue('id_product');
        $productMaster = ProductMaster::loadByProductId($id_product);
        if (empty($productMaster)) {
            $productMaster = new ProductMaster();
        }
        $productMaster->master_id = Tools::getValue('lemonink_product_master_id');
        $productMaster->id_product = $id_product;

        if (isset($productMaster->id)) {
            if (empty($productMaster->master_id)) {
                $productMaster->delete();
            } else {
                $productMaster->update();
            }
        } elseif (!empty($productMaster->master_id)) {
            $productMaster->add();
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $newOrderStatus = $params["newOrderStatus"];
        
        if ($newOrderStatus->id == (int)Configuration::get('LEMONINK_WATERMARKING_ORDER_STATE')) {
            $id_order = $params["id_order"];

            $order = new Order($id_order);

            foreach ($order->getProducts() as $id_order_detail => $product) {
                if (!Transaction::getIdFromIdOrderDetail($id_order_detail)) {
                    $productMaster = ProductMaster::loadByProductId($product['product_id']);

                    if (!empty($productMaster)) {
                        $remoteMaster = $this->getApiClient()->find('master', $productMaster->master_id);

                        $customer = new Customer((int) $order->id_customer);
                        $orderLanguage = new Language((int) $order->id_lang);

                        $remoteUser = $this->getApiClient()->find('user', 'me');

                        $remoteTransaction = new LemonInk\Models\Transaction();
                        $remoteTransaction->setMasterId($remoteMaster->getId());
                        $remoteTransaction->setWatermarkParams(
                            $this->watermarkParams(
                                $remoteUser->getWatermarkParams(),
                                $id_order,
                                $customer
                            )
                        );

                        $this->getApiClient()->save($remoteTransaction);
                        
                        $transaction = new Transaction();
                        $transaction->id_order_detail = $id_order_detail;
                        $transaction->transaction_id = $remoteTransaction->getId();
                        $transaction->token = $remoteTransaction->getToken();
                        $transaction->formats = implode(',', $remoteMaster->getFormats());
                        $transaction->save();
                    }
                }
            }

            $this->sendDownloadLinks($order);
        }
    }

    protected function sendDownloadLinks($order)
    {
        $links = $this->getDownloadLinks($order);

        if (!empty($links)) {
            $customer = new Customer((int) $order->id_customer);

            $data = array(
                '{lastname}' => $customer->lastname,
                '{firstname}' => $customer->firstname,
                '{id_order}' => (int) $order->id,
                '{order_name}' => $order->getUniqReference(),
                '{nbProducts}' => count(Transaction::getProductsByOrder($order)),
                '{virtualProducts}' => $links,
            );

            $orderLanguage = new Language((int) $order->id_lang);
            Mail::Send(
                (int) $order->id_lang,
                'download_product',
                $this->l(
                    'The virtual product that you bought is available for download',
                    false,
                    $orderLanguage->locale
                ),
                $data,
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                _PS_MAIL_DIR_,
                false,
                (int) $order->id_shop
            );
        }
    }

    protected function getDownloadLinks($order)
    {
        $products = Transaction::getProductsByOrder($order);

        $html = '';

        if (!empty($products)) {
            $html .= '<ul>';
            foreach ($products as $product) {
                $html .= '<li>';
                $html .= $product['product_name'] . ': ';
                foreach ($product['lemoninkTransaction']->getFormats() as $format) {
                    $html .= '<a href="' . $product['lemoninkTransaction']->getUrl($format) .
                        '">' . Tools::strtoupper($format) . '</a> ';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    public function hookDisplayOrderDetail($params)
    {
        $order = $params["order"];
        $links = $this->getDownloadLinks($order);

        $html = '';

        if (!empty($links)) {
            $html .= '<article id="lemonink-downloads" class="box">';
                $html .= '<h4>' . $this->l('Your downloads') . '</h4>';
                $html .= $links;
            $html .= '</article>';
        }

        return $html;
    }

    private function watermarkParams($paramNames, $orderId, $customer)
    {
        $params = array();

        foreach ($paramNames as $paramName) {
            $params[$paramName] = $this->watermarkParam($paramName, $orderId, $customer);
        }

        return $params;
    }

    private function watermarkParam($paramName, $orderId, $customer)
    {
        switch ($paramName) {
            case 'order_number':
                return $orderId;
            case 'obfuscated_customer_email':
                return $this->obfuscateEmail($customer->email);
            case 'customer_email':
                return $customer->email;
            case 'customer_first_name':
                return $customer->firstname;
            case 'customer_last_name':
                return $customer->lastname;
            case 'customer_name':
                return implode(" ", array($customer->firstname, $customer->lastname));
            case 'obfuscated_customer_name':
                return implode(" ", array($customer->firstname, Tools::substr($customer->lastname, 0, 1) . "."));
        }
    }

    private function obfuscateEmail($email)
    {
        $parts = explode('@', $email);
        $parts[0] = Tools::substr($parts[0], 0, 1) . '***' . Tools::substr($parts[0], -1, 1);
        return implode('@', $parts);
    }

    private function getApiClient()
    {
        return new LemonInk\Client(Configuration::get('LEMONINK_API_KEY'));
    }
}
