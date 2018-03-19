<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Entity\Order;
use Symfony\Component\HttpFoundation\Request;


class DriverController extends AbstractController
{
    const PICK_UP = 'pick_up';
    const DELIVERY = 'delivery';

    public function home(Application $app)
    {
        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $routeName = '';
            $today = '';
            $results = null;
            $bus_no = 0;
            $driver = $app->user()->getId();
            $Bus = $app['eccube.repository.bus']->findOneBy(array('Customer' => $app->user()));
            if ($Bus != null) {
                $receiverSchedule = $app['eccube.repository.route_detail']->getDriverReceiverSchedule($driver, date('Y-m-d'));
                $farmerSchedule = $app['eccube.repository.route_detail']->getDriverFarmerSchedule($driver, date('Y-m-d'));
                foreach ($receiverSchedule as $receiver) {
                    $flag = false;
                    foreach ($farmerSchedule as $farmer) {
                        if ($farmer['bus_stop_id'] == $receiver['bus_stop_id']) {
                            $receiver['farmer_total_amount'] = $farmer['total_amount'];
                            $results[] = $receiver;
                            $flag = true;
                            break;
                        }
                    }
                    if (!$flag) {
                        $receiver['farmer_total_amount'] = 0;
                        $results[] = $receiver;
                    }

                }
                if (sizeof($results) > 0) {
                    $routeName = $results[0]['route_name'];
                    $today = date('Y') . '年' . date('m') . '月' . date('d') . '日';
                }
                $bus_no = $Bus->getBusNo();
            }

            return $app->render('Driver\driver_home.twig', array('BusSchedule' => $results, 'route_name' => $routeName, 'today' => $today, 'bus_no' => $bus_no));
        } else {
            return $app->redirect($app->url('mypage_login'));
        }

    }

    public function home_tomorrow(Application $app)
    {
        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $routeName = '';
            $today = '';
            $results = null;
            $bus_no = 0;
            $Bus = $app['eccube.repository.bus']->findOneBy(array('Customer' => $app->user()));
            if ($Bus != null) {
                $driver = $app->user()->getId();
                $tomorrow = date("Y-m-d", strtotime('tomorrow'));
                $receiverSchedule = $app['eccube.repository.route_detail']->getDriverReceiverSchedule($driver, $tomorrow);
                $farmerSchedule = $app['eccube.repository.route_detail']->getDriverFarmerSchedule($driver, $tomorrow);


                foreach ($receiverSchedule as $receiver) {
                    $flag = false;
                    foreach ($farmerSchedule as $farmer) {
                        if ($farmer['bus_stop_id'] == $receiver['bus_stop_id']) {
                            $receiver['farmer_total_amount'] = $farmer['total_amount'];
                            $results[] = $receiver;
                            $flag = true;
                            break;
                        }
                    }
                    if (!$flag) {
                        $receiver['farmer_total_amount'] = 0;
                        $results[] = $receiver;
                    }
                }
                if (sizeof($results) > 0) {
                    $routeName = $results[0]['route_name'];
                    $tomorrow = explode('-', $tomorrow);
                    $today = $tomorrow[0] . '年' . $tomorrow[1] . '月' . $tomorrow[2] . '日';
                }
                $bus_no = $Bus->getBusNo();
            }


            return $app->render('Driver\driver_home.twig', array('BusSchedule' => $results, 'route_name' => $routeName, 'today' => $today, 'bus_no' => $bus_no));
        } else {
            return $app->redirect($app->url('mypage_login'));
        }
    }

    public function detail_cargo(Application $app, $id, Request $request)
    {
        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $BusStop = $app['eccube.repository.bus_stop']->find($id);
            $orderIdArr = null;
            $status = 11;
            $results = null;
            $OrderStatus = $app['eccube.repository.master.order_status']->find($status);
            if ($BusStop != null) {
                $results = $app['eccube.repository.route_detail']->getDriveCargoDetail($id, date('Y-m-d'));
                foreach ($results as $result) {
                    $orderIdArr[] = $result['order_id'];
                }
                $delivery = $request->get('delivery');
                if ($delivery == self::DELIVERY) {
                    foreach ($orderIdArr as $orderId) {
                        /* @var $Order Order*/
                        $Order = $app['eccube.repository.order']->find($orderId);
                        $Order->setOrderStatus($OrderStatus);
                        $app['orm.em']->persist($Order);
                    }
                    $app['orm.em']->flush();
                    return $app->redirect($app->url('driver_home'));
                }
            }
            return $app->render('Driver\driver_detail_cargo.twig', array('BusStop' => $BusStop, 'results' => $results));
        } else {
            return $app->redirect($app->url('mypage_login'));
        }
    }

    public function detail_cargo_pick(Application $app, $id, Request $request)
    {
        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $BusStop = $app['eccube.repository.bus_stop']->find($id);
            $results = null;
            $orderIdArr = null;
            $status = 13;
            $OrderStatus = $app['eccube.repository.master.order_status']->find($status);
            if ($BusStop != null) {
                $results = $app['eccube.repository.route_detail']->getDriveCargoPick($id, date('Y-m-d'));
                foreach ($results as $result) {
                    $orderIdArr[] = $result['order_id'];
                }
            }
            $pick_up = $request->get('pick_up');
            if ($pick_up == self::PICK_UP) {
                foreach ($orderIdArr as $orderId) {
                    /* @var $Order Order*/
                    $Order = $app['eccube.repository.order']->find($orderId);
                    $Order->setOrderStatus($OrderStatus);
                    $app['orm.em']->persist($Order);
                }
                $app['orm.em']->flush();
                return $app->redirect($app->url('driver_home'));
            }

            return $app->render('Driver\driver_detail_pick.twig', array('BusStop' => $BusStop, 'results' => $results));
        } else {
            return $app->redirect($app->url('mypage_login'));
        }
    }

}
