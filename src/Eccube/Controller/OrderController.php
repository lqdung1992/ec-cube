<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/28/2018
 * Time: 9:11 PM
 */

namespace Eccube\Controller;


use Eccube\Application;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
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
        // Todo: check permission: ROLE_FARMER|ALL
        /** @var Order $Order */
        $Order = $orderRepo->find($id);
        if (!$Order) {
            throw new NotFoundHttpException();
        }

        $mode = $request->get('mode');
        if (!$mode) {
            switch ($Order->getOrderStatus()->getId()) {
                case OrderStatus::ORDER_PREPARE:
                    $mode = 'prepare';
                    break;
                case OrderStatus::ORDER_PICKUP:
                    $mode = 'pickup';
                    break;
            }
        }
        $masterDate = $app['eccube.repository.master.receiptable_date']->findAllWithKeyAsId();

        switch ($mode) {
            // for role recipient
            case "check_status":
            // for role farmer
            case "prepare":
                $customer = $app->user();
                $farms = $Order->getFarm();
                // is farmer and creator
                if ($app->isGranted('ROLE_FARMER') && $farms[0]->getId() == $customer->getId() && $Order->getOrderStatus()->getId() != $app['config']['order_deliv']) {
                    $OrderStatus = $app['eccube.repository.master.order_status']->find(OrderStatus::ORDER_PREPARE);
                    $orderRepo->changeStatus($id, $OrderStatus);
                }

                return $app->render('Order/pickup.twig', array('Order' => $Order, 'days' => $masterDate));
                break;

            case "pickup":
                return $app->render('Order/pickup_done.twig', array('Order' => $Order, 'days' => $masterDate));
                break;
            default:
                break;
        }

        return $app->render('Order/index.twig', array('Order' => $Order, 'days' => $masterDate));
    }

}
