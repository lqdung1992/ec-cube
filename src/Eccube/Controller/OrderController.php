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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws Application\AuthenticationCredentialsNotFoundException
     */
    public function index(Application $app, Request $request, $id)
    {
        // Todo: check permission: ROLE_FARMER|ALL
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $app->redirect($app->url('mypage_login'));
        }
        /** @var OrderRepository $orderRepo */
        $orderRepo = $app['eccube.repository.order'];
        /** @var Order $Order */
        $Order = $orderRepo->find($id);
        if (!$Order) {
            throw new NotFoundHttpException();
        }

        $mode = $request->get('mode');
        if (!$mode) {
            switch ($Order->getOrderStatus()->getId()) {
                case OrderStatus::ORDER_PICKUP:
                    $mode = 'pickup';
                    break;
                case OrderStatus::PICKUP_DONE:
                    $mode = 'pickup_done';
                    break;
                case OrderStatus::DELIVERY_DONE:
                    $mode = 'delivery';
                    break;
                case OrderStatus::ORDER_DONE:
                    $mode = 'complete';
                    break;
            }
        }
        $masterDate = $app['eccube.repository.master.receiptable_date']->findAllWithKeyAsId();

        switch ($mode) {
            // for role recipient
            case "check_status":
            // for role farmer
            case "pickup":
                $customer = $app->user();
                $farms = $Order->getFarm();
                // is farmer and creator
                if ($app->isGranted('ROLE_FARMER') && $farms[0]->getId() == $customer->getId() && $Order->getOrderStatus()->getId() != $app['config']['order_deliv']) {
                    $OrderStatus = $app['eccube.repository.master.order_status']->find(OrderStatus::ORDER_PREPARE);
                    $orderRepo->changeStatus($id, $OrderStatus);
                }

                return $app->render('Order/pickup.twig', array('Order' => $Order, 'days' => $masterDate));
                break;

            case "pickup_done":
                return $app->render('Order/pickup_done.twig', array('Order' => $Order, 'days' => $masterDate));
                break;
            case "delivery":
                return $app->render('Order/delivery.twig', array('Order' => $Order, 'days' => $masterDate));
                break;
            case "complete":
                return $app->render('Order/complete.twig', array('Order' => $Order, 'days' => $masterDate));
                break;
            default:
                break;
        }

        return $app->render('Order/index.twig', array('Order' => $Order, 'days' => $masterDate));
    }

}
