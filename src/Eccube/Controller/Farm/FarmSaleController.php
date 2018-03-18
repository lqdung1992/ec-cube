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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sale(Application $app, Request $request)
    {
        if (!$app->isGranted(CustomerRole::FARMER)) {
            return $app->redirect($app->url('mypage_login'));
        }
        $Customer = $app->user();
        //need change to mysql
        $results = $app['eccube.repository.order']->getSaleByMonth($Customer->getId());

        return $app->render('Farm/farm_sale.twig', array('results' => $results));
    }
}