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

class DriverController extends AbstractController
{
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

    public function detail_cargo(Application $app, $id)
    {
        if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
            $BusStop = $app['eccube.repository.bus_stop']->find($id);
            $results = null;
            if ($BusStop != null) {
                $results = $app['eccube.repository.route_detail']->getDriveCargoDetail($id, date('Y-m-d'));
            }
            return $app->render('Driver\driver_detail_cargo.twig', array('BusStop' => $BusStop, 'results' => $results));
        } else {
            return $app->redirect($app->url('mypage_login'));
        }
    }

    public function detail_cargo_pick(Application $app, Request $request)
    {
        return $app->render('index.twig');
    }

    public function detail_cargo_active(Application $app, $id)
    {
    }
}
