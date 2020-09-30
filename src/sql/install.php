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
