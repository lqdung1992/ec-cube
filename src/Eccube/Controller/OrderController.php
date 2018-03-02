<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/28/2018
 * Time: 9:11 PM
 */

namespace Eccube\Controller;


use Eccube\Application;
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
        $Order = $orderRepo->findWithStatus($id, $app['config']['order_new']);
        $mode = $request->get('mode');
        if (!$Order) {
            $Order = $orderRepo->find($id);
            switch ($Order->getOrderStatus()->getId()) {
                case $app['config']['order_deliv']:
                    $mode = 'shipping';
                    break;
                case 9:
                    break;
            }
//            throw new NotFoundHttpException();
        }
        $masterDate = $app['eccube.repository.master.receiptable_date']->findAllWithKeyAsId();

        switch ($mode) {
            // for role recipient
            case "check_status":
            case "shipping":
                $customer = $app->user();
                $farms = $Order->getFarm();
                // is farmer and creator
                if ($app->isGranted('ROLE_FARMER') && $farms[0]->getId() == $customer->getId() && $Order->getOrderStatus()->getId() != $app['config']['order_deliv']) {
                    $OrderStatus = $app['eccube.repository.master.order_status']->find($app['config']['order_deliv']);
                    $orderRepo->changeStatus($id, $OrderStatus);
                    return $app->render('Order/pickup.twig', array('Order' => $Order, 'days' => $masterDate));
                } else {
                    $app->addError('You have not permission to do this, please login as farmer', 'front');
                }
                break;
            case "pickup":
                break;
            default:
                break;
        }


        return $app->render('Order/index.twig', array('Order' => $Order, 'days' => $masterDate));
    }

}
