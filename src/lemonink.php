<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/LemonInkProductMaster.php');

class Lemonink extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'lemonink';
        $this->tab = 'others';
        $this->version = '0.0.1';
        $this->author = 'LemonInk';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('LemonInk');
        $this->description = $this->l('9Descasdaasdasdasd asd asd asd asd asd asd asd');

        $this->confirmUninstall = $this->l('Are you sure? Files set up to use LemonInk won\'t be available for purchase anymore.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('LEMONINK_API_KEY');

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
            $apiKey = strval(Tools::getValue('LEMONINK_API_KEY'));
            $unlink = strval(Tools::getValue('LEMONINK_UNLINK'));
            
            if ($unlink) {
                Configuration::updateValue('LEMONINK_API_KEY', null);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            } elseif ($apiKey && !empty($apiKey)) {
                Configuration::updateValue('LEMONINK_API_KEY', $apiKey);
            } else {
                $output .= $this->displayError($this->l('This API key is invalid. Please make sure that you\'ve copied the correct value'));
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
        $productMaster = LemonInkProductMaster::loadByProductId($id_product);
        $this->context->smarty->assign(array(
            'master_id' => $productMaster->master_id
        ));
        return $this->display(__FILE__, 'views/templates/admin/product_form.tpl');
    }

    public function hookActionProductUpdate()
    {
        $id_product = Tools::getValue('id_product');
        $productMaster = LemonInkProductMaster::loadByProductId($id_product);
        $productMaster->master_id = Tools::getValue('lemonink_product_master_id');
        $productMaster->id_product = $id_product;

        if(!empty($productMaster) && isset($productMaster->id)){
            $productMaster->update();
        } else {
            $productMaster->add();
        }
    }
}
