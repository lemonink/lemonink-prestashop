<?php

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
        $query->where('id_product = ' . (int) $id_product);

        $id_lemonink_product_master = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

        if (!empty($id_lemonink_product_master)) {
            return new ProductMaster($id_lemonink_product_master);
        }
    }
}
