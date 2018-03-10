<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bus
 */
class Bus extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $bus_no;

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
     * @var \Eccube\Entity\Master\Route
     */
    private $Route;

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
     * @return integer
     */
    public function getBusNo()
    {
        return $this->bus_no;
    }

    public function getBusName()
    {
        return $this->bus_no . '号車';
    }

    /**
     * @param integer $bus_no
     */
    public function setBusNo($bus_no)
    {
        $this->bus_no = $bus_no;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
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
     * @return \Eccube\Entity\Master\Route
     */
    public function getRoute()
    {
        return $this->Route;
    }

    /**
     * @param \Eccube\Entity\Master\Route $Route
     */
    public function setRoute($Route)
    {
        $this->Route = $Route;
    }


}
