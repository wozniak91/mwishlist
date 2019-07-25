<?php

class Wishlist extends ObjectModel
{
    
    /** @var int Customer ID */
    public $id_customer = null;

    /** @var string secure_key */
    public $secure_key;
    
    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public $productsNb = 0;

    public static $definition = array(
        'table' => 'mwishlist',
        'primary' => 'id_wishlist',
        'fields' => array(
            'id_customer' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'secure_key' =>         array('type' => self::TYPE_STRING, 'size' => 32),
            'date_add' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'products' =>           array('type' => self::HAS_MANY, 'field' => 'id_product', 'object' => 'Product', 'association' => 'mwishlist_products'),
        )
    );


    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id);

        if (!is_null($id_lang)) {
            $this->id_lang = (int)(Language::getLanguage($id_lang) !== false) ? $id_lang : Configuration::get('PS_LANG_DEFAULT');
        }

        $this->productsNb = $this->getProductNb();
    }

    public function getProductNb() {

        return (int)Db::getInstance()->getValue('SELECT count(*) FROM `'._DB_PREFIX_.'mwishlist_products` WHERE id_wishlist = ' . (int)$this->id );
    }

    public function checkProductStatus($id_product) {

        $sql = 'SELECT count(wp.id_product) FROM `'._DB_PREFIX_.'mwishlist_products` wp 
            LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = wp.`id_product`)
            WHERE wp.`id_product` = ' . (int)$id_product . ' AND p.`active` = 1 AND wp.`id_wishlist` = '.(int)$this->id;
         
        return Db::getInstance()->getValue($sql);
    }

    public function addProduct($id_product) {

        if($this->checkProductStatus($id_product))
            return false;

        if(Db::getInstance()->getValue('SELECT count(id_product) FROM `'._DB_PREFIX_.'product` WHERE id_product = ' . (int)$id_product)) {
            if($result = Db::getInstance()->insert('mwishlist_products', array(
                'id_wishlist'   => (int)$this->id,
                'id_product'    => (int)$id_product,
            ))) {
                $this->productsNb = $this->getProductNb();
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }

        
    }


    public static function getWishlistIdByCustomerId($id_customer) {

        $sql = 'SELECT id_wishlist FROM `'._DB_PREFIX_.'mwishlist` WHERE id_customer = '.(int)$id_customer;

        return Db::getInstance()->getValue($sql);
    }

    public function removeProduct($id_product) {

        if($result = Db::getInstance()->delete('mwishlist_products', 'id_wishlist = '.(int)$this->id.' AND id_product = '.(int)$id_product)) {
            $this->productsNb = $this->getProductNb();
            return $result;
        } else {
            return false;
        }
        
    }

    public function getProducts($id_lang, $id_shop) {

        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nb_days_new_product)) {
            $nb_days_new_product = 20;
        }

        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) AS quantity'.(Combination::isFeatureActive() ? ', IFNULL(product_attribute_shop.id_product_attribute, 0) AS id_product_attribute,
        product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '').', pl.`description`, pl.`description_short`, pl.`available_now`,
        pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image` id_image,
        il.`legend` as legend, m.`name` AS manufacturer_name, cl.`name` AS category_default,
        DATEDIFF(product_shop.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00",
        INTERVAL '.(int)$nb_days_new_product.' DAY)) > 0 AS new, product_shop.price AS orderprice
        FROM `'._DB_PREFIX_.'mwishlist_products` wp
        LEFT JOIN `'._DB_PREFIX_.'product` p
        ON p.`id_product` = wp.`id_product`
        '.Shop::addSqlAssociation('product', 'p').
        (Combination::isFeatureActive() ? ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$id_shop.')':'').'
        '.Product::sqlStock('p', 0).'
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
        ON (product_shop.`id_category_default` = cl.`id_category`
        AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
        ON (p.`id_product` = pl.`id_product`
        AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
        ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$id_shop.')
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il
        ON (image_shop.`id_image` = il.`id_image`
        AND il.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
        ON m.`id_manufacturer` = p.`id_manufacturer`
        WHERE wp.`id_wishlist` = '.(int)$this->id.' AND product_shop.`id_shop` = '.(int)$id_shop.' AND p.`active` = 1';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        return Product::getProductsProperties($id_lang, $result);

    }

    public static function getProductLikesCount($id_product) {

        return Db::getInstance()->getValue('SELECT count(id_product) FROM `'._DB_PREFIX_.'mwishlist_products` WHERE id_product = '.(int)$id_product);

    }

}