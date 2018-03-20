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
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Notification;
use Eccube\Repository\NewsRepository;
use Eccube\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FarmSaleController extends AbstractController
{
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
    public function setDiscount(Application $app, $id)
    {
        if (!$app->isGranted(CustomerRole::FARMER)) {
            return $app->redirect($app->url('mypage_login'));
        }

        if ($id == null) {
            throw new NotFoundHttpException();
        }

        $Customer = $app['eccube.repository.customer']->find($id);

        return $app->render('Farm/set_farm_discount.twig', array('TargetCustomer' => $Customer));
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