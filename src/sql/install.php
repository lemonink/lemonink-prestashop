<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lemonink_product_masters` (
    `id_lemonink_product_master` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` INT( 11 ) UNSIGNED NOT NULL,
    `master_id` varchar(36) NOT NULL,
    PRIMARY KEY  (`id_lemonink_product_master`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lemonink_transactions` (
    `id_lemonink_transaction` int(11) NOT NULL AUTO_INCREMENT,
    `id_order_detail` INT( 11 ) UNSIGNED NOT NULL,
    `transaction_id` varchar(36) NOT NULL,
    `token` varchar(36) NOT NULL,
    `formats` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_lemonink_transaction`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
