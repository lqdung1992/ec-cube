<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/10/2018
 * Time: 10:16 PM
 */

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Customer;
use Eccube\Entity\Notification;

class NotificationRepository extends EntityRepository
{
    /** @var Application */
    private $app;
    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    public function insertNotice(Customer $Customer, Customer $TargetCustomer, $type, $id, $productName = null)
    {
        $message = $this->createMessage($Customer, $type, $productName);
        $notice = new Notification();
        $notice->setDelFlg(Constant::DISABLED)
            ->setCustomer($Customer)
            ->setIdLink($id)
            ->setNotice($message)
            ->setTargetCustomer($TargetCustomer)
            ->setNoticeType($type);
        $this->getEntityManager()->persist($notice);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Customer $Customer
     * @param $type Notification::type
     * @param null $productName
     * @return null|string
     */
    public function createMessage(Customer $Customer, $type, $productName = null)
    {
        $message = null;
        switch ($type) {
            case Notification::TYPE_ORDER:
                $message = $this->app->trans('notification.product.detail', array('%name%' => $Customer->__toString(), '%product_name%' => $productName));
                break;
            case Notification::TYPE_PROFILE:
                $message = $this->app->trans('notification.farm.profile', array('%name%' => $Customer->__toString()));
                break;
            case Notification::TYPE_PRODUCT:
                $message = $this->app->trans('nofitication.order.new', array('%name%' => $Customer->__toString()));
                break;
        }

        return $message;
    }
}
