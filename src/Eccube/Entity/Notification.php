<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/10/2018
 * Time: 10:16 PM
 */


namespace Eccube\Entity;

/**
 * Notification
 */
class Notification extends \Eccube\Entity\AbstractEntity
{
    const TYPE_ORDER = 1;
    const TYPE_PROFILE = 2;
    const TYPE_PRODUCT = 3;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $notice;

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
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /** @var int */
    private $notice_type;

    /** @var int */
    private $id_link;

    /** @var Customer */
    private $TargetCustomer;

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
     * Set create_date
     *
     * @param  \DateTime $createDate
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param  \DateTime $updateDate
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set del_flg
     *
     * @param  integer  $delFlg
     * @return $this
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get del_flg
     *
     * @return integer
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * @return string
     */
    public function getNotice()
    {
        return $this->notice;
    }

    /**
     * @param string $notice
     * @return $this
     */
    public function setNotice($notice)
    {
        $this->notice = $notice;

        return $this;
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
     * @return $this
     */
    public function setCustomer($Customer)
    {
        $this->Customer = $Customer;
        return $this;
    }

    /**
     * @return int
     */
    public function getNoticeType()
    {
        return $this->notice_type;
    }

    /**
     * @param int $notice_type
     * @return $this
     */
    public function setNoticeType($notice_type)
    {
        $this->notice_type = $notice_type;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdLink()
    {
        return $this->id_link;
    }

    /**
     * @param int $id_link
     * @return $this
     */
    public function setIdLink($id_link)
    {
        $this->id_link = $id_link;
        return $this;
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
     * @return $this
     */
    public function setTargetCustomer($TargetCustomer)
    {
        $this->TargetCustomer = $TargetCustomer;
        return $this;
    }
}
