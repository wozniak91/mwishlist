<?php

class Wishlist extends ObjectModel
{
    
    /** @var int Customer ID */
    public $id_customer = null;
    
    /** @var int Guest ID */
    public $id_guest;

    /** @var string secure_key */
    public $secure_key;
    
    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $definition = array(
        'table' => 'wishlist',
        'primary' => 'id_wishlist',
        'fields' => array(
            'id_customer' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'secure_key' =>            array('type' => self::TYPE_STRING, 'size' => 32),
            'date_add' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>                array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
}