<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\Route;

/**
 * RouteDetail
 */
class RouteDetail extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var integer
     */
    private $move_time;

    /**
     * @var integer
     */
    private $work_time;

    /**
     * @var integer
     */
    private $arrive_time;

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
     * @var BusStop
     */
    private $BusStop;

    /**
     * @var Route
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
     * @return integer
     */
    public function getMoveTime()
    {
        return $this->move_time;
    }

    /**
     * @param integer $move_time
     */
    public function setMoveTime($move_time)
    {
        $this->move_time = $move_time;
    }

    /**
     * @return integer
     */
    public function getWorkTime()
    {
        return $this->work_time;
    }

    /**
     * @param integer $work_time
     */
    public function setWorkTime($work_time)
    {
        $this->work_time = $work_time;
    }

    /**
     * @return integer
     */
    public function getArriveTime()
    {
        return $this->arrive_time;
    }

    /**
     * @param integer $arrive_time
     */
    public function setArriveTime($arrive_time)
    {
        $this->arrive_time = $arrive_time;
    }

    /**
     * @return BusStop
     */
    public function getBusStop()
    {
        return $this->BusStop;
    }

    /**
     * @param BusStop $BusStop
     */
    public function setBusStop($BusStop)
    {
        $this->BusStop = $BusStop;
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
     * @return Route
     */
    public function getRoute()
    {
        return $this->Route;
    }

    /**
     * @param Route $Route
     */
    public function setRoute($Route)
    {
        $this->Route = $Route;
    }


    private $total_amount;
    private $total_farmer_amount;

    /**
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * @param mixed $total_amount
     */
    public function setTotalAmount($total_amount)
    {
        $this->total_amount = $total_amount;
    }

    /**
     * @return mixed
     */
    public function getTotalFarmerAmount()
    {
        return $this->total_farmer_amount;
    }

    /**
     * @param mixed $total_farmer_amount
     */
    public function setTotalFarmerAmount($total_farmer_amount)
    {
        $this->total_farmer_amount = $total_farmer_amount;
    }

}
