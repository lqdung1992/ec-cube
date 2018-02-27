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


namespace Eccube\Controller\Admin\Schedule;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Bus;
use Eccube\Entity\BusStop;
use Symfony\Component\HttpFoundation\Request;

class ScheduleController extends AbstractController
{
    public function index(Application $app, Request $request, $parent_id = null, $id = null)
    {

    }

    public function bus(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $Bus = $app['eccube.repository.bus']->find($id);
        } else {
            $Bus = new Bus();
        }
        $builder = $app['form.factory']->createBuilder('admin_schedule_bus', $Bus);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Bus = $form->getData();
            // DB登録
            $app['orm.em']->persist($Bus);
            $app['orm.em']->flush($Bus);
            $app->addSuccess('admin.schedule.bus.save.complete', 'admin');
            return $app->redirect($app->url('admin_schedule_bus'));
        }

        $Buses = $app['eccube.repository.bus']->findAll();

        return $app->render('Schedule/bus.twig', array(
            'form' => $form->createView(),
            'Buses'  => $Buses,
            'CurrentBus'  => $Bus
        ));
    }

    public function busStop(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $BusStop = $app['eccube.repository.bus_stop']->find($id);
        } else {
            $BusStop = new BusStop();
        }
        $builder = $app['form.factory']->createBuilder('admin_schedule_bus_stop', $BusStop);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $BusStop = $form->getData();
            $status = $app['eccube.repository.bus_stop']->save($BusStop);
            if ($status)
                $app->addSuccess('admin.schedule.bus_stop.save.complete', 'admin');
            else
                $app->addSuccess('admin.schedule.bus_stop.save.error', 'admin');

            return $app->redirect($app->url('admin_schedule_bus_stop'));
        }

        $BusStops = $app['eccube.repository.bus_stop']->findBy(array(), array('rank' => 'DESC'));

        return $app->render('Schedule/bus_stop.twig', array(
            'form' => $form->createView(),
            'BusStops'  => $BusStops,
            'CurrentBusStop'  => $BusStop
        ));
    }

    public function deleteBus(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $Bus = $app['eccube.repository.bus']->find($id);
        if (!$Bus) {
            $app->deleteMessage();
            return $app->redirect($app->url('admin_schedule_bus'));
        }

        log_info('Bus削除開始', array($id));
        $app['orm.em']->remove($Bus);
        $app['orm.em']->flush($Bus);
        log_info('Bus削除完了', array($id));
        $app->addSuccess('admin.schedule.bus.delete.complete', 'admin');

        return $app->redirect($app->url('admin_schedule_bus'));
    }

    public function deleteBusStop(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $BusStop = $app['eccube.repository.bus_stop']->find($id);
        if (!$BusStop) {
            $app->deleteMessage();
            return $app->redirect($app->url('admin_schedule_bus_stop'));
        }

        log_info('Bus Stop 削除開始', array($id));
        $app['orm.em']->remove($BusStop);
        $app['orm.em']->flush($BusStop);
        log_info('Bus Stop 削除完了', array($id));
        $app->addSuccess('admin.schedule.bus_stop.delete.complete', 'admin');

        return $app->redirect($app->url('admin_schedule_bus_stop'));
    }

    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $ranks = $request->request->all();
            foreach ($ranks as $busStopId => $rank) {
                /* @var $Category \Eccube\Entity\Category */
                $BusStop = $app['eccube.repository.bus_stop']
                    ->find($busStopId);
                $BusStop->setRank($rank);
                $app['orm.em']->persist($BusStop);
            }
            $app['orm.em']->flush();
        }
        return true;
    }

}
