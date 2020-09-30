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
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderLazyArray;

/**
 * Class Transaction.
 */
class Transaction extends ObjectModel
{
    /** @var int Order Detail ID which transaction belongs to */
    public $id_order_detail = null;

    /** @var int LemonInk Transaction ID */
    public $transaction_id = null;

    /** @var int LemonInk Transaction token */
    public $token = null;

    /** @var int LemonInk Transaction formats */
    public $formats = null;

    protected $_remoteTransaction = null;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = [
        'table' => 'lemonink_transactions',
        'primary' => 'id_lemonink_transaction',
        'fields' => [
            'id_order_detail' => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
            'transaction_id' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 36],
            'token' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 36],
            'formats' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 255],
        ],
    ];

    /**
     * Checks whether a LemonInk Transaction exists for a given Order Detail ID.
     *
     * @param int $id_order_detail Order Detail ID
     *
     * @return int Transaction id for given Order Detail
     */
    public static function getIdFromIdOrderDetail($id_order_detail)
    {
        $query = new DbQuery();
        $query->select('id_lemonink_transaction');
        $query->from('lemonink_transactions');
        $query->where('id_order_detail = ' . $id_order_detail);

        return Db::getInstance()->getValue($query);
    }

    /**
     * Gets order details with attached Transaction (and only those)
     * 
     * @param Order $order
     * 
     * @return array
     */
    public static function getProductsByOrder($order)
    {
        $orderLazyArray = new OrderLazyArray($order);

        $products = $orderLazyArray->getProducts();
        $result = [];

        foreach ($products as $product) {
            $id = Transaction::getIdFromIdOrderDetail($product["id_order_detail"]);
            
            if ($id) {
                $transaction = new Transaction($id);
                $product['lemoninkTransaction'] = $transaction;
                $result[] = $product;
            }
        }

        return $result;
    }

    /**
     * Returns array of supported formats for this Transaction
     * 
     * @return array
     */
    public function getFormats()
    {
        return explode(",", $this->formats);
    }

    /**
     * Returns a download url for a given format
     * 
     * @param string $format
     * 
     * @return string
     */
    public function getUrl($format)
    {
        return join("/", [
            \LemonInk\Models\Transaction::DOWNLOADS_ENDPOINT,
            $this->token,
            join(".", [$this->transaction_id, $format])
        ]);
    }
}
