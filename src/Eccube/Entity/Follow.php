<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 17/03/2018
 * Time: 10:30 PM
 */
namespace Eccube\Entity;


/**
 * Class Follow
 * @package Eccube\Entity
 */
class Follow  extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var Customer
     */
    private $Customer;

    /**
     * @var Customer
     */
    private $TargetCustomer;

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

    /**
     * @param \DateTime $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param \DateTime $update_date
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * @param Customer $Customer
     */
    public function setCustomer($Customer)
    {
        $this->Customer = $Customer;
    }

    /**
     * @return Customer
     */
    public function getTargetCustomer()
    {
        return $this->TargetCustomer;
    }

    /**
     * @param Customer $TargetCustomer
     */
    public function setTargetCustomer($TargetCustomer)
    {
        $this->TargetCustomer = $TargetCustomer;
    }
}
