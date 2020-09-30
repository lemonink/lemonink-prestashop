<?php

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lemonink_product_masters`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lemonink_transactions`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
