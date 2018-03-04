<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/5/2018
 * Time: 3:19 PM
 */
namespace Eccube\Entity\Master;

/**
 * BusArea
 */
class BusArea extends \Eccube\Entity\AbstractEntity
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
     * @return BusArea
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
     * @return BusArea
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
     * @return BusArea
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
