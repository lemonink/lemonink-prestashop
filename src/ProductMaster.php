<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use PrestaShop\Module\LemonInk;

/**
 * Class ProductMaster.
 */
class ProductMaster extends ObjectModel
{
    /** @var int Product ID which master belongs to */
    public $id_product = null;

    /** @var int LemonInk Master ID */
    public $master_id = null;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = [
        'table' => 'lemonink_product_masters',
        'primary' => 'id_lemonink_product_master',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
            'master_id' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 36],
        ],
    ];

    /**
     * Returns ProductMaster ID for a given Product ID.
     *
     * @since 1.5.0
     *
     * @param int $id_product Product ID
     *
     * @return ProductMaster $id_lemonink_product_master ProductMaster
     */
    public static function loadByProductId($id_product)
    {
        $query = new DbQuery();
        $query->select('id_lemonink_product_master');
        $query->from('lemonink_product_masters');
        $query->where('id_product = ' . $id_product);

        $id_lemonink_product_master = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

        if (!empty($id_lemonink_product_master)) {
            return new ProductMaster($id_lemonink_product_master);
        }
    }
}
