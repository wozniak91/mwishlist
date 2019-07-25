<?php
/**
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'mwishlist/classes/Wishlist.php');

class Mwishlist extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'mwishlist';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Rafał Woźniak';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        $this->controllers = array('wishlist');

        parent::__construct();

        $this->displayName = $this->l('Wishlist');
        $this->description = $this->l('Adds a list with favorite products');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('MWISHLIST_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayTop') &&
            $this->registerHook('displayProductListWishlist');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MWISHLIST_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitMwishlistModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

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
        $helper->submit_action = 'submitMwishlistModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
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
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'MWISHLIST_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'MWISHLIST_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'MWISHLIST_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'MWISHLIST_LIVE_MODE' => Configuration::get('MWISHLIST_LIVE_MODE', true),
            'MWISHLIST_ACCOUNT_EMAIL' => Configuration::get('MWISHLIST_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'MWISHLIST_ACCOUNT_PASSWORD' => Configuration::get('MWISHLIST_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
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
        // $this->context->controller->addJS($this->_path.'/views/js/front/mwishlist.js');
        // $this->context->controller->addCSS($this->_path.'/views/css/front/mwishlist.css');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front/mwishlist.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front/mwishlist.css');
    }

    public function hookDisplayTop()
    {

        if($id_wishlist = Wishlist::getWishlistIdByCustomerId($this->context->customer->id)) {

            $wishlist = new Wishlist((int)$id_wishlist);
            $this->context->cookie->__set('id_wishlist', $wishlist->id);

        } elseif ($this->context->cookie->id_wishlist) {
            $id_wishlist = (int)$this->context->cookie->id_wishlist;
            $wishlist = new Wishlist($id_wishlist);
        } else {
            $wishlist = new Wishlist;
        }

        $this->context->smarty->assign('wishlist', $wishlist);

        return $this->display(__FILE__, 'views/templates/hook/mwishlist.tpl');
    }

    public function hookDisplayProductListWishlist($params) {

        $id_product = Validate::isLoadedObject($params['product']) ? $params['product']->id : $params['product']['id_product'];

        if(Validate::isLoadedObject($params['product'])) {
            
            $id_product = $params['product']->id;
            $fromProductPage = true;

        } else {
            $id_product = $params['product']['id_product'];
            $fromProductPage = false;
        }

        
        $this->context->smarty->clearCompiledTemplate('mwishlist_reviews.tpl');

        if($this->context->cookie->id_wishlist) {
            $id_wishlist = (int)$this->context->cookie->id_wishlist;
            $wishlist = new Wishlist($id_wishlist);
            $isLiked = $wishlist->checkProductStatus($id_product);
        } else {
            $isLiked = false;
        }

        $this->context->smarty->assign([
            'id_product' => $id_product,
            'isLiked' => $isLiked,
            'fromProductPage' => $fromProductPage,
            'likesCount' => Wishlist::getProductLikesCount($id_product)
        ]);

        return $this->display(__FILE__, 'views/templates/hook/mwishlist_reviews.tpl');
    }
}
