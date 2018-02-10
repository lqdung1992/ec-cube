<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 5:56 PM
 */

namespace Eccube\Entity;

/**
 * Class ChangePassword
 * @package Eccube\Entity
 */
class ChangePassword
{
    protected $email;
    protected $old_password;

    protected $new_password;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->old_password;
    }

    /**
     * @param mixed $old_password
     */
    public function setOldPassword($old_password)
    {
        $this->old_password = $old_password;
    }

    /**
     * @return mixed
     */
    public function getNewPassword()
    {
        return $this->new_password;
    }

    /**
     * @param mixed $new_password
     */
    public function setNewPassword($new_password)
    {
        $this->new_password = $new_password;
    }
}
