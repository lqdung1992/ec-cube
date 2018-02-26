<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusStop
 */
class BusStop extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var string
     */
    private $address;

    /**
     * @var integer
     */
    private $del_flg;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\Master\BusArea
     */
    private $BusArea;

    /**
     * @var \Eccube\Entity\Master\BusStatus
     */
    private $BusStatus;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param integer $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return integer
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * @param integer $del_flg
     */
    public function setDelFlg($del_flg)
    {
        $this->del_flg = $del_flg;
    }

    /**
     * @return Master\BusArea
     */
    public function getBusArea()
    {
        return $this->BusArea;
    }

    /**
     * @param Master\BusArea $BusArea
     */
    public function setBusArea($BusArea)
    {
        $this->BusArea = $BusArea;
    }

    /**
     * @return Master\BusStatus
     */
    public function getBusStatus()
    {
        return $this->BusStatus;
    }

    /**
     * @param Master\BusStatus $BusStatus
     */
    public function setBusStatus($BusStatus)
    {
        $this->BusStatus = $BusStatus;
    }


}
