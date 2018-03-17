<?php
/**
 * Created by PhpStorm.
 * User: MSI GV62
 * Date: 2/21/2018
 * Time: 8:25 PM
 */

namespace Eccube\Entity;


class ProductRate extends AbstractEntity
{
    private $like_count;
    private $delicious_count;
    private $fresh_count;
    private $vivid_count;
    private $aroma_count;
    private $id;
    /** @var Product */
    private $Product;

    /**
     * @return mixed
     */
    public function getLikeCount()
    {
        return $this->like_count;
    }

    /**
     * @param mixed $like_count
     */
    public function setLikeCount($like_count)
    {
        $this->like_count = $like_count;
    }

    /**
     * @return mixed
     */
    public function getDeliciousCount()
    {
        return $this->delicious_count;
    }

    /**
     * @param mixed $delicious_count
     */
    public function setDeliciousCount($delicious_count)
    {
        $this->delicious_count = $delicious_count;
    }

    /**
     * @return mixed
     */
    public function getFreshCount()
    {
        return $this->fresh_count;
    }

    /**
     * @param mixed $fresh_count
     */
    public function setFreshCount($fresh_count)
    {
        $this->fresh_count = $fresh_count;
    }

    /**
     * @return mixed
     */
    public function getVividCount()
    {
        return $this->vivid_count;
    }

    /**
     * @param mixed $vivid_count
     */
    public function setVividCount($vivid_count)
    {
        $this->vivid_count = $vivid_count;
    }

    /**
     * @return mixed
     */
    public function getAromaCount()
    {
        return $this->aroma_count;
    }

    /**
     * @param mixed $aroma_count
     */
    public function setAromaCount($aroma_count)
    {
        $this->aroma_count = $aroma_count;
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
    public function setProduct($Product)
    {
        $this->Product = $Product;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}