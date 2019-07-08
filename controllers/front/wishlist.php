<?php


class MwishlistWishlistModuleFrontController extends ModuleFrontController
{
    public $context;
    public $wishlist;

    /**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
        
        $this->display_column_left = false;

        $this->context = Context::getContext();
        
        if($id_wishlist = $this->context->cookie->id_wishlist) {

            $this->wishlist = new Wishlist((int)$id_wishlist);
        } else {

            $this->wishlist = new Wishlist;
        }

        parent::initContent();
        $this->wishlist->products = $this->wishlist->getProducts($this->context->language->id, $this->context->shop->id);
        $this->context->smarty->assign('wishlist', $this->wishlist);
		$this->setTemplate('wishlist.tpl');
	}

    /**
     * @TODO uses redirectAdmin only if !$this->ajax
     * @return bool
     */
    public function postProcess()
    {
        try {
            if ($this->ajax) {
                // from ajax-tab.php
                $action = Tools::getValue('action');
                // no need to use displayConf() here
                if (!empty($action) && method_exists($this, 'ajaxProcess'.Tools::toCamelCase($action))) {

                    $return = $this->{'ajaxProcess'.Tools::toCamelCase($action)}();

                    return $return;
                } 
            } 
        } catch (PrestaShopException $e) {
            $this->errors[] = $e->getMessage();
		}
		
        return false;
    }

    public function ajaxProcessAddProduct()
    {

        if($id_wishlist = $this->context->cookie->id_wishlist) {

            $wishlist = new Wishlist((int)$id_wishlist);
        } else {

            $wishlist = new Wishlist;
            $wishlist->id_customer = $this->context->customer->id;
            $wishlist->secure_key = md5(uniqid(rand(), true));
            $wishlist->add();
            $this->context->cookie->id_wishlist = $wishlist->id;
        }

        if(Tools::getIsset('id_product')) {
            $id_product = Tools::getValue('id_product');
            $wishlist->addProduct((int)$id_product);
        }

        $result = [
            'hasError' => false,
            'wishlist' => $wishlist,
        ];
        
        return die(Tools::jsonEncode($result));
    }
    
    public function ajaxProcessRemoveProduct()
    {

        if($id_wishlist = $this->context->cookie->id_wishlist) {

            $wishlist = new Wishlist((int)$id_wishlist);

            if(Tools::getIsset('id_product')) {
                $id_product = Tools::getValue('id_product');
                $wishlist->removeProduct((int)$id_product);
            }
            $result = [
                'hasError' => false,
                'wishlist' => $wishlist,
            ];

        } else {

            $result = [
                'hasError' => true,
                'error' => $this->module->l('You do not have a wish list')
            ];   
        }
        
        return die(Tools::jsonEncode($result));
	}

    public function ajaxProcessToggleProduct()
    {

        if($id_wishlist = $this->context->cookie->id_wishlist) {

            $wishlist = new Wishlist((int)$id_wishlist);
            $status = false;

            if(Tools::getIsset('id_product')) {
                $id_product = Tools::getValue('id_product');
                if($wishlist->checkProductStatus($id_product)) {
                    $status = $wishlist->removeProduct((int)$id_product);
                } else {
                    $status = $wishlist->addProduct((int)$id_product);
                }
            }
            $result = [
                'hasError' => !$status,
                'wishlist' => $wishlist,
            ];

        } else {

            $result = [
                'hasError' => true,
                'error' => $this->module->l('You do not have a wish list')
            ];   
        }
        
        return die(Tools::jsonEncode($result));
    }

}
