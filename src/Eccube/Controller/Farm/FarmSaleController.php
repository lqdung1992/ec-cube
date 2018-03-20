<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/10/2018
 * Time: 11:16 PM
 */

namespace Eccube\Controller\Farm;


use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\FarmerDiscount;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Notification;
use Eccube\Repository\NewsRepository;
use Eccube\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FarmSaleController extends AbstractController
{
    const DISCOUNT = 'discount';
    const DISCOUNT_REMOVE = 'discount_remove';

    /**
     * @param Application $app
     * @param Request $request
     * @throws
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sale(Application $app, Request $request)
    {
        if (!$app->isGranted(CustomerRole::FARMER)) {
            return $app->redirect($app->url('mypage_login'));
        }
        $year = $request->get('year');
        if ($year == null) {
            $year = date('Y');
        }
        $Customer = $app->user();
        //need change to mysql
        $results = $app['eccube.repository.order']->getSaleByMonth($Customer->getId(), $year);
        return $app->render('Farm/farm_sale.twig', array('results' => $results, 'year' => $year));
    }

    /**
     * @param Application $app
     * @throws
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function discount(Application $app)
    {
        if (!$app->isGranted(CustomerRole::FARMER)) {
            return $app->redirect($app->url('mypage_login'));
        }

        return $app->render('Farm/farm_discount_search.twig', array());
    }

    /**
     * @param Application $app
     * @param Request $request
     * @throws
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function setDiscount(Application $app, $id, Request $request)
    {
        if (!$app->isGranted(CustomerRole::FARMER)) {
            return $app->redirect($app->url('mypage_login'));
        }

        if ($id == null) {
            throw new NotFoundHttpException();
        }
        $Customer = $app['eccube.repository.customer']->find($id);
        $Farmer = $app->user();
        $discount = $request->get('discount');
        $discountNumber = $request->get('discount_number');
        $discountRemove = $request->get('discount_remove');
        /* @var \Eccube\Entity\FarmerDiscount $FarmerDiscount */
        $FarmerDiscount = $app['eccube.repository.farmer_discount']->findOneBy(array('Farmer' => $Farmer, 'Customer' => $Customer));
        $em = $app['orm.em'];
        $discountValue = 0;
        if ($FarmerDiscount != null) {
            $discountValue = $FarmerDiscount->getDiscount();
        }
        if ($discount == self::DISCOUNT) {
            if ($FarmerDiscount == null) {
                $FarmerDiscount = new FarmerDiscount();
                $FarmerDiscount->setDiscount($discountNumber);
                $FarmerDiscount->setFarmer($Farmer);
                $FarmerDiscount->setCustomer($Customer);
            } else {
                $FarmerDiscount->setDiscount($discountNumber);
            }

            $Farmer->addFarmerDiscount($FarmerDiscount);
            $em->persist($FarmerDiscount);
            $em->flush();

            return $app->redirect($app->url('farm_discount'));
        }

        if ($discountRemove == self::DISCOUNT_REMOVE) {
            $em->remove($FarmerDiscount);
            $em->flush();

            return $app->redirect($app->url('farm_discount'));
        }

        return $app->render('Farm/set_farm_discount.twig', array('TargetCustomer' => $Customer, 'discount' => $discountValue));
    }

    public function getCustomer(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $name = $request->get('name');
            $Customers = $app['eccube.repository.customer']->getReceiverByName($name);
            $html = $app->render('Farm/receiver_search_result.twig', array('Customers' => $Customers));
            return $html;
        }

        return true;
    }
}