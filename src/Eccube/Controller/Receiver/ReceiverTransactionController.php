<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/18/2018
 * Time: 2:51 PM
 */

namespace Eccube\Controller\Receiver;


use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Repository\OrderRepository;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

class ReceiverTransactionController extends AbstractController
{
    public function index(Application $app, Request $request)
    {
        if (!$app->isGranted(CustomerRole::RECIPIENT)) {
            return $app->redirect($app->url('mypage_login'));
        }
        /** @var Customer $Customer */
        $Customer = $app->user();

        /** @var OrderRepository $orderRepos */
        $orderRepos = $app['eccube.repository.order'];
        $arrStatusTransaction = array(
            $app['config']['order_new'],
            OrderStatus::ORDER_PICKUP,
            OrderStatus::PICKUP_DONE,
            OrderStatus::DELIVERY_DONE,
        );
        /** @var Order[] $orderTransaction */
        $orderTransaction = $orderRepos->getQueryBuilderByReceiver($Customer, $arrStatusTransaction)->getQuery()->getResult();
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
        $queryBuilder = $orderRepos->getQueryBuilderByReceiver($Customer, $arrStatusComplete);
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
        $masterDate = $app['eccube.repository.master.receiptable_date']->findAllWithKeyAsId();

        return $app->render('Receiver/receiver_transaction.twig', array(
            'OrderTransaction' => $orderTransaction,
            'formOrderBy' => $formOrderBy->createView(),
            'OrderComplete' => $orderComplete,
            'days' => $masterDate
        ));
    }
}