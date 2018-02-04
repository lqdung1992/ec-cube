<?php
namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Common\Constant;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Farmer
 * @package Eccube\Entity
 */
class Farmer extends \Eccube\Entity\AbstractEntity implements UserInterface
{
    /**
     * @var integer
     */
    private $id;

    /** @var string */
    private $name;

    /**
     * @var string
     */
    private $company_name;

    /**
     * @var string
     */
    private $zip01;

    /**
     * @var string
     */
    private $zip02;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $addr01;

    /**
     * @var string
     */
    private $addr02;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $tel;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var string
     */
    private $reset_key;

    /**
     * @var \DateTime
     */
    private $reset_expire;

    /**
     * @var \Eccube\Entity\Master\CustomerStatus
     */
    private $Status;

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
     * @var \Eccube\Entity\Master\Sex
     */
    private $Sex;

    private $maker;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDelFlg(Constant::DISABLED);
    }

    /**
     * @return mixed
     */
    public function getMaker()
    {
        return $this->maker;
    }

    /**
     * @param mixed $maker
     */
    public function setMaker($maker)
    {
        $this->maker = $maker;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_FARMER');
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    // TODO: できればFormTypeで行いたい
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields'  => 'email',
            'message' => '既に利用されているメールアドレスです'
        )));
    }

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
     * Set name01
     *
     * @param  string   $name01
     * @return $this
     */
    public function setName($name01)
    {
        $this->name = $name01;

        return $this;
    }

    /**
     * Get name01
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set company_name
     *
     * @param  string   $companyName
     * @return $this
     */
    public function setCompanyName($companyName)
    {
        $this->company_name = $companyName;

        return $this;
    }

    /**
     * Get company_name
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * Set zip01
     *
     * @param  string   $zip01
     * @return $this
     */
    public function setZip01($zip01)
    {
        $this->zip01 = $zip01;

        return $this;
    }

    /**
     * Get zip01
     *
     * @return string
     */
    public function getZip01()
    {
        return $this->zip01;
    }

    /**
     * Set zip02
     *
     * @param  string   $zip02
     * @return $this
     */
    public function setZip02($zip02)
    {
        $this->zip02 = $zip02;

        return $this;
    }

    /**
     * Get zip02
     *
     * @return string
     */
    public function getZip02()
    {
        return $this->zip02;
    }

    /**
     * Set zipcode
     *
     * @param  string   $zipcode
     * @return $this
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Set addr01
     *
     * @param  string   $addr01
     * @return $this
     */
    public function setAddr01($addr01)
    {
        $this->addr01 = $addr01;

        return $this;
    }

    /**
     * Get addr01
     *
     * @return string
     */
    public function getAddr01()
    {
        return $this->addr01;
    }

    /**
     * Set addr02
     *
     * @param  string   $addr02
     * @return $this
     */
    public function setAddr02($addr02)
    {
        $this->addr02 = $addr02;

        return $this;
    }

    /**
     * Get addr02
     *
     * @return string
     */
    public function getAddr02()
    {
        return $this->addr02;
    }

    /**
     * Set email
     *
     * @param  string   $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set tel01
     *
     * @param  string   $tel01
     * @return $this
     */
    public function setTel($tel01)
    {
        $this->tel = $tel01;

        return $this;
    }

    /**
     * Get tel01
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set password
     *
     * @param  string   $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param  string   $salt
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set secret_key
     *
     * @param  string   $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secret_key = $secretKey;

        return $this;
    }

    /**
     * Get secret_key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * Set resetKey
     *
     * @param  string   $resetKey
     * @return $this
     */
    public function setResetKey($resetKey)
    {
        $this->reset_key = $resetKey;

        return $this;
    }

    /**
     * Get resetKey
     *
     * @return string
     */
    public function getResetKey()
    {
        return $this->reset_key;
    }

    /**
     * Set resetExpire
     *
     * @param  \DateTime   $resetExpire
     * @return $this
     */
    public function setResetExpire($resetExpire)
    {
        $this->reset_expire = $resetExpire;

        return $this;
    }

    /**
     * Get resetExpire
     *
     * @return \DateTime
     */
    public function getResetExpire()
    {
        return $this->reset_expire;
    }

    /**
     * @param Master\CustomerStatus|null $status
     * @return $this
     */
    public function setStatus(\Eccube\Entity\Master\CustomerStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\CustomerStatus
     */
    public function getStatus()
    {
        return $this->Status;
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
     * Set Sex
     *
     * @param  \Eccube\Entity\Master\Sex $sex
     * @return $this
     */
    public function setSex(\Eccube\Entity\Master\Sex $sex = null)
    {
        $this->Sex = $sex;

        return $this;
    }

    /**
     * Get Sex
     *
     * @return \Eccube\Entity\Master\Sex
     */
    public function getSex()
    {
        return $this->Sex;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }
}
