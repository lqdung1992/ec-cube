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


namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Master\Route;

/**
 * RouteDetailRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RouteDetailRepository extends EntityRepository
{
    public function save(\Eccube\Entity\RouteDetail $RouteDetail)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if (!$RouteDetail->getId()) {
                $rank = $this->createQueryBuilder('c')
                    ->select('MAX(c.rank)')
                    ->getQuery()
                    ->getSingleScalarResult();
                if (!$rank) {
                    $rank = 0;
                }
                $RouteDetail->setRank($rank + 1);
                $RouteDetail->setDelFlg(0);

                $em->createQueryBuilder()
                    ->update('Eccube\Entity\RouteDetail', 'c')
                    ->set('c.rank', 'c.rank + 1')
                    ->where('c.rank > :rank')
                    ->setParameter('rank', $rank)
                    ->getQuery()
                    ->execute();
            }

            $em->persist($RouteDetail);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    public function getRoute($Route) {
        $result = null;
        try {
            $qb = $this->createQueryBuilder('rd')
                ->select('r.id, r.name, r.rank')
                ->leftJoin('rd.Route', 'r')
                ->orderBy('r.rank', 'DESC')
                ->groupBy('rd.Route');

            if (!is_null($Route)) {
                $qb->where('rd.Route = :Route')->setParameter('Route', $Route);
            }

            $Routes = $qb->getQuery()
                        ->getResult();

            foreach ($Routes as $item) {
                $Route = new Route();
                $Route->setName($item['name']);
                $Route->setId($item['id']);
                $Route->setRank($item['rank']);
                $result[] = $Route;
            }

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRouteDetailByRouteId($Route) {
        $result = null;
        try {
            $qb = $this->createQueryBuilder('rd')
                ->select('rd')
                ->leftJoin('rd.Route', 'r')
                ->orderBy('rd.move_time', 'DESC')
                ->where('rd.Route = :Route')
                ->setParameter('Route', $Route);
            $RouteDetails = $qb->getQuery()->getResult();
            return $RouteDetails;
        } catch (\Exception $e) {
            return null;
        }
    }
}
