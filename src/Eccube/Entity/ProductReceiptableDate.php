<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/13/2018
 * Time: 11:16 AM
 */

namespace Eccube\Entity;

use Eccube\Entity\Master\ReceiptableDate;

/**
 * Class ProductReceiptableDate
 * @package Eccube\Entity
 */
class ProductReceiptableDate extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $product_id;

    /**
     * @var int
     */
    private $date_id;

    /** @var  ReceiptableDate */
    private $ReceiptableDate;

    /** @var  int */
    private $max_quantity;

    /**
     * @var \Eccube\Entity\Product
     */
    private $Product;

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param int $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * @param Product $Product
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;
    }

    /**
     * @return int
     */
    public function getMaxQuantity()
    {
        return $this->max_quantity;
    }

    /**
     * @param int $max_quantity
     */
    public function setMaxQuantity($max_quantity)
    {
        $this->max_quantity = $max_quantity;
    }

    /**
     * @return int
     */
    public function getDateId()
    {
        return $this->date_id;
    }

    /**
     * @param int $date_id
     */
    public function setDateId($date_id)
    {
        $this->date_id = $date_id;
    }

    /**
     * @return ReceiptableDate
     */
    public function getReceiptableDate()
    {
        return $this->ReceiptableDate;
    }

    /**
     * @param ReceiptableDate $ReceiptableDate
     */
    public function setReceiptableDate($ReceiptableDate)
    {
        $this->ReceiptableDate = $ReceiptableDate;
    }
}