<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/5/2018
 * Time: 3:19 PM
 */
namespace Eccube\Entity\Master;

/**
 * RouteScheduleStatus
 */
class RouteScheduleStatus extends \Eccube\Entity\AbstractEntity
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return RouteScheduleStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set rank
     *
     * @param  integer $rank
     * @return RouteScheduleStatus
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return RouteScheduleStatus
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
