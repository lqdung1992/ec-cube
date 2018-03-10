<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RouteSchedule
 */
class RouteSchedule extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $schedule_status;

    /**
     * @var Bus
     */
    private $Bus;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var integer
     */
    private $del_flg;

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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getScheduleStatus()
    {
        return $this->schedule_status;
    }

    /**
     * @param string $schedule_status
     */
    public function setScheduleStatus($schedule_status)
    {
        $this->schedule_status = $schedule_status;
    }

    /**
     * @return Bus
     */
    public function getBus()
    {
        return $this->Bus;
    }

    /**
     * @param Bus $Bus
     */
    public function setBus($Bus)
    {
        $this->Bus = $Bus;
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
}
