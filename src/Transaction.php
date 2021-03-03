<?php

use PrestaShop\Module\LemonInk;

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
        $products = $order->getCartProducts();
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
