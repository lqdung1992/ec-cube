<?php

namespace Eccube\Entity\Master;

/**
 * SearchType
 */
class SearchType extends \Eccube\Entity\AbstractEntity
{
    const SEARCH_ITEM = 1;
    CONST SEARCH_FARMER = 2;
    CONST SEARCH_METHOD = 3;
    CONST SEARCH_HISTORY = 4;
    CONST SEARCH_OTHER = 5;

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
     * @return SearchType
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
     * @return SearchType
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
     * @return SearchType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
