<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/28/2018
 * Time: 9:11 PM
 */

namespace Eccube\Controller;


use Eccube\Application;
use Eccube\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function index(Application $app, Request $request, $id)
    {
        /** @var OrderRepository $orderRepo */
        $orderRepo = $app['eccube.repository.order'];
        $Order = $orderRepo->findOneBy(array('id' => $id, 'OrderStatus' => $app['config']['order_new']));
        if (!$Order) {
            throw new NotFoundHttpException();
        }
        $masterDate = $app['eccube.repository.master.receiptable_date']->findAllWithKeyAsId();

        return $app->render('Order/index.twig', array('Order' => $Order, 'days' => $masterDate));
    }

}