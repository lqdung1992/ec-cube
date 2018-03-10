<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/11/2018
 * Time: 1:00 AM
 */

namespace Eccube\Controller\Farm;


use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FarmHomeController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request, $id = null)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }
        $TargetCustomer = null;
        if ($id) {
            $TargetCustomer = $app['eccube.repository.customer']->find($id);
            if (!$TargetCustomer) {
                throw new NotFoundHttpException();
            }
        }
        if (!$TargetCustomer) {
            $TargetCustomer = $Customer;
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $app['eccube.repository.product'];
        $productList = $productRepo->getProductQueryBuilderByCustomer($TargetCustomer)->getQuery()->getResult();
        /** @var OrderRepository $orderRepos */
        $orderRepos = $app['eccube.repository.order'];
        $arrStatusTransaction = array(
            $app['config']['order_new'],
        );
        /** @var Order[] $orderTransaction */
        $orderTransaction = $orderRepos->getQueryBuilderByOwner($TargetCustomer, $arrStatusTransaction)->getQuery()->getResult();
        $orderTransactionByDate = array();
        if ($orderTransaction) {
            foreach ($orderTransaction as $order) {
                if ($order->getReceiptableDate()) {
                    $orderTransactionByDate[$order->getReceiptableDate()->format('m月d日')][] = $order;
                }
            }
        }

        $arrStatusDelivery = array(
            OrderStatus::ORDER_PREPARE,
            OrderStatus::ORDER_PICKUP,
        );
        /** @var Order[] $orderDelivery */
        $orderDelivery = $orderRepos->getQueryBuilderByOwner($TargetCustomer, $arrStatusDelivery)->getQuery()->getResult();
        $orderDeliveryByDate = array();
        if ($orderDelivery) {
            foreach ($orderDelivery as $order) {
                if ($order->getReceiptableDate()) {
                    $orderDeliveryByDate[$order->getReceiptableDate()->format('m月d日')][] = $order;
                }
            }
        }

        $arrStatusComplete = array(
            OrderStatus::ORDER_DONE,
        );

        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createNamedBuilder('', 'home_complete');
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $formOrderBy = $builder->getForm();
        $formOrderBy->handleRequest($request);
        $queryBuilder = $orderRepos->getQueryBuilderByOwner($TargetCustomer, $arrStatusComplete);
        $queryBuilder->resetDQLPart('orderBy');
        $data = $formOrderBy->getData();
        $orderBy = isset($data['order_by']) ? $data['order_by'] : Order::SORT_BY_NEW;
        switch ($orderBy) {
            case Order::SORT_BY_TOTAL:
                $queryBuilder->orderBy('o.total', 'DESC');
                break;
            case Order::SORT_BY_NEW:
            default:
                $queryBuilder->orderBy('o.update_date', 'DESC');
                break;

        }
        /** @var Order[] $orderComplete */
        $orderComplete = $queryBuilder->getQuery()->getResult();

        return $app->render('Farm/farm_home.twig', array(
            'Products' => $productList,
            'TargetCustomer' => $TargetCustomer,
            'OrderTransactionByDate' => $orderTransactionByDate,
            'OrderDeliveryByDate' => $orderDeliveryByDate,
            'formOrderBy' => $formOrderBy->createView(),
            'OrderComplete' => $orderComplete,
        ));
    }
}
