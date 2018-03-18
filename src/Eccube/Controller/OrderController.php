<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/28/2018
 * Time: 9:11 PM
 */

namespace Eccube\Controller;


use Eccube\Application;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Repository\BusStopRepository;
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
    public function index(Application $app, Request $request, $id = null)
    {
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $app->redirect($app->url('mypage_login'));
        }
        if (is_null($id)) {
            $id = $request->get('id');
            if (!$id) {
                throw new NotFoundHttpException();
            }
        }

        /** @var OrderRepository $orderRepo */
        $orderRepo = $app['eccube.repository.order'];
        /** @var Order $Order */
        $Order = $orderRepo->find($id);
        if (!$Order) {
            throw new NotFoundHttpException();
        }

        // Check permission to action
        /** @var Customer $Customer */
        $Customer = $app->user();
        if (!$app->isGranted(CustomerRole::DRIVER)) {
            if ($Order->getCustomer()->getId() != $Customer->getId() && $Order->isFarmer($Customer)) {
                throw new NotFoundHttpException();
            }
        }

        $mode = $request->get('mode');
        if (!$mode) {
            switch ($Order->getOrderStatus()->getId()) {
                case OrderStatus::ORDER_PICKUP:
                    $mode = 'pickup';
                    break;
                case OrderStatus::PICKUP_DONE:
                    $mode = 'delivery';
                    break;
                case OrderStatus::DELIVERY_DONE:
                    $mode = 'receive';
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
            case "prepare":
            case "pickup":
                // is farmer and creator
                if ($app->isGranted(CustomerRole::FARMER)
                    && $Order->isFarmer($Customer)
                    && $Order->getOrderStatus()->getId() == $app['config']['order_new']) {
                    $OrderStatus = $app['eccube.repository.master.order_status']->find(OrderStatus::ORDER_PICKUP);
                    $orderRepo->changeStatus($id, $OrderStatus);
                }
                /** @var BusStopRepository $busStopRepo */
                $busStopRepo = $app['eccube.repository.bus_stop'];
                $busStop = $busStopRepo->getByOrder($Order);
                return $app->render('Order/pickup.twig', array('Order' => $Order, 'days' => $masterDate, 'busStop' => $busStop));
                break;
            case "delivery":
            case "pickup_done":
                // Todo: driver confirm to change status
                return $app->render('Order/pickup_done.twig', array('Order' => $Order, 'days' => $masterDate));
                break;

            case "receive":
            case "receiver_confirm":
                if ($request->getMethod() == "POST") {
                    if ($app->isGranted(CustomerRole::RECIPIENT)
                        && $Order->getOrderStatus()->getId() == OrderStatus::DELIVERY_DONE
                        && $Order->getCustomer()->getId() == $Customer->getId()
                    ) {
                        $OrderStatus = $app['eccube.repository.master.order_status']->find(OrderStatus::ORDER_DONE);
                        $orderRepo->changeStatus($id, $OrderStatus);

                        return $app->redirect($app->url('order', array('id' => $id, 'mode' => 'complete')));
                    }
                }
                /** @var BusStopRepository $busStopRepo */
                $busStopRepo = $app['eccube.repository.bus_stop'];
                $busStop = $busStopRepo->getByOrder($Order);
                return $app->render('Order/receive.twig', array('Order' => $Order, 'days' => $masterDate, 'busStop' => $busStop));
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
