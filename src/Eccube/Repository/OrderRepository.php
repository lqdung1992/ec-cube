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

use Eccube\Entity\Customer;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Util\Str;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Silex\Tests\Provider\ValidatorServiceProviderTest\Constraint\Custom;
use Doctrine\ORM\Query\ResultSetMapping;


/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository extends EntityRepository
{
    protected $app;

    public function setApplication($app)
    {
        $this->app = $app;
    }

    public function changeStatus($orderId, \Eccube\Entity\Master\OrderStatus $Status)
    {
        $Order = $this
            ->find($orderId)
            ->setOrderStatus($Status)
        ;

        switch ($Status->getId()) {
            // Todo: set commit date with new status
            // Maybe OrderStatus::ORDER_PICKUP
            case OrderStatus::ORDER_PICKUP: // 発送済へ
                $Order->setCommitDate(new \DateTime());
                break;
            case '6': // 入金済へ
                $Order->setPaymentDate(new \DateTime());
                break;
        }

        $em = $this->getEntityManager();
        $em->persist($Order);
        $em->flush();
    }

    /**
     *
     * @param  array        $searchData
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('o');

        $joinedCustomer = false;

        // order_id_start
        if (isset($searchData['order_id_start']) && Str::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('o.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && Str::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('o.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // status
        if (!empty($searchData['status']) && $searchData['status']) {
            $qb
                ->andWhere('o.OrderStatus = :status')
                ->setParameter('status', $searchData['status']);
        }

        // name
        if (isset($searchData['name']) && Str::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('CONCAT(o.name01, o.name02) LIKE :name')
                ->setParameter('name', '%' . $searchData['name'] . '%');
        }

        // kana
        if (isset($searchData['kana']) && Str::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(o.kana01, o.kana02) LIKE :kana')
                ->setParameter('kana', '%' . $searchData['kana'] . '%');
        }

        // email
        if (isset($searchData['email']) && Str::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('o.email = :email')
                ->setParameter('email', $searchData['email']);
        }

        // tel
        if (isset($searchData['tel01']) && Str::isNotBlank($searchData['tel01'])) {
            $qb
                ->andWhere('o.tel01 = :tel01')
                ->setParameter('tel01', $searchData['tel01']);
        }
        if (isset($searchData['tel02']) && Str::isNotBlank($searchData['tel02'])) {
            $qb
                ->andWhere('o.tel02 = :tel02')
                ->setParameter('tel02', $searchData['tel02']);
        }
        if (isset($searchData['tel03']) && Str::isNotBlank($searchData['tel03'])) {
            $qb
                ->andWhere('o.tel03 = :tel03')
                ->setParameter('tel03', $searchData['tel03']);
        }

        // birth
        if (!empty($searchData['birth_start']) && $searchData['birth_start']) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $date = $searchData['birth_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth >= :birth_start')
                ->setParameter('birth_start', $date);
        }
        if (!empty($searchData['birth_end']) && $searchData['birth_end']) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $date = clone $searchData['birth_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth < :birth_end')
                ->setParameter('birth_end', $date);
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            if (!$joinedCustomer) {
                $qb->leftJoin('o.Customer', 'c');
                $joinedCustomer = true;
            }

            $sexs = array();
            foreach ($searchData['sex'] as $sex) {
                $sexs[] = $sex->getId();
            }

            $qb
                ->andWhere($qb->expr()->in('c.Sex', ':sexs'))
                ->setParameter('sexs', $sexs);
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = array();
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.create_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.create_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // create_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && Str::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && Str::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && Str::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_name'] . '%');
        }

        // Order By
        $qb->addOrderBy('o.update_date', 'DESC');

        return $qb;
    }


    /**
     *
     * @param  array        $searchData
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('o');

        // order_id_start
        if (isset($searchData['order_id_start']) && Str::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('o.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }
        // multi
        if (isset( $searchData['multi']) && Str::isNotBlank($searchData['multi'])) {
            $multi = preg_match('/^\d+$/', $searchData['multi']) ? $searchData['multi'] : null;
            $qb
                ->andWhere('o.id = :multi OR o.name01 LIKE :likemulti OR o.name02 LIKE :likemulti OR ' .
                           'o.kana01 LIKE :likemulti OR o.kana02 LIKE :likemulti OR o.company_name LIKE :likemulti')
                ->setParameter('multi', $multi)
                ->setParameter('likemulti', '%' . $searchData['multi'] . '%');
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && Str::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('o.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // status
        $filterStatus = false;
        if (!empty($searchData['status']) && $searchData['status']) {
            $qb
                ->andWhere('o.OrderStatus = :status')
                ->setParameter('status', $searchData['status']);
            $filterStatus = true;
        }
        if (!empty($searchData['multi_status']) && count($searchData['multi_status'])) {
            $qb
                ->andWhere($qb->expr()->in('o.OrderStatus', ':multi_status'))
                ->setParameter('multi_status', $searchData['multi_status']->toArray());
            $filterStatus = true;
        }
        if (!$filterStatus) {
            // 購入処理中は検索対象から除外
            $OrderStatuses = $this->getEntityManager()
                ->getRepository('Eccube\Entity\Master\OrderStatus')
                ->findNotContainsBy(array('id' => $this->app['config']['order_processing']));
            $qb->andWhere($qb->expr()->in('o.OrderStatus', ':status'))
                ->setParameter('status', $OrderStatuses);
        }

        // name
        if (isset($searchData['name']) && Str::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('CONCAT(o.name01, o.name02) LIKE :name')
                ->setParameter('name', '%' . $searchData['name'] . '%');
        }

        // kana
        if (isset($searchData['kana']) && Str::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(o.kana01, o.kana02) LIKE :kana')
                ->setParameter('kana', '%' . $searchData['kana'] . '%');
        }

        // email
        if (isset($searchData['email']) && Str::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('o.email like :email')
                ->setParameter('email', '%' . $searchData['email'] . '%');
        }

        // tel
        if (isset($searchData['tel']) && Str::isNotBlank($searchData['tel'])) {
            $qb
                ->andWhere('CONCAT(o.tel01, o.tel02, o.tel03) LIKE :tel')
                ->setParameter('tel', '%' . $searchData['tel'] . '%');
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            $qb
                ->andWhere($qb->expr()->in('o.Sex', ':sex'))
                ->setParameter('sex', $searchData['sex']->toArray());
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = array();
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.order_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.order_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // payment_date
        if (!empty($searchData['payment_date_start']) && $searchData['payment_date_start']) {
            $date = $searchData['payment_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date >= :payment_date_start')
                ->setParameter('payment_date_start', $date);
        }
        if (!empty($searchData['payment_date_end']) && $searchData['payment_date_end']) {
            $date = clone $searchData['payment_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date < :payment_date_end')
                ->setParameter('payment_date_end', $date);
        }

        // commit_date
        if (!empty($searchData['commit_date_start']) && $searchData['commit_date_start']) {
            $date = $searchData['commit_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date >= :commit_date_start')
                ->setParameter('commit_date_start', $date);
        }
        if (!empty($searchData['commit_date_end']) && $searchData['commit_date_end']) {
            $date = clone $searchData['commit_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date < :commit_date_end')
                ->setParameter('commit_date_end', $date);
        }


        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && Str::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && Str::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && Str::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_name'] . '%');
        }

        // Order By
        $qb->orderBy('o.update_date', 'DESC');
        $qb->addorderBy('o.id', 'DESC');

        return $qb;
    }


    /**
     * @param  \Eccube\Entity\Customer $Customer
     * @return QueryBuilder
     */
    public function getQueryBuilderByCustomer(\Eccube\Entity\Customer $Customer)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.Customer = :Customer')
            ->setParameter('Customer', $Customer);

        // Order By
        $qb->addOrderBy('o.id', 'DESC');

        return $qb;
    }

    /**
     * 新規受付一覧の取得
     *
     * @return \Eccube\Entity\Order[]
     */
    public function getNew()
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->where('o.OrderStatus <> :OrderStatus')
            ->setParameter('OrderStatus', $this->app['config']['order_cancel'])
            ->setMaxResults(10)
            ->orderBy('o.create_date', 'DESC');

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * 会員の合計購入金額を取得、回数を取得
     *
     * @param  \Eccube\Entity\Customer $Customer
     * @param  array $OrderStatuses
     * @return QueryBuilder
     */
    public function getCustomerCount(\Eccube\Entity\Customer $Customer, array $OrderStatuses)
    {
        $result = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS buy_times, SUM(o.total)  AS buy_total')
            ->where('o.Customer = :Customer')
            ->andWhere('o.OrderStatus in (:OrderStatuses)')
            ->setParameter('Customer', $Customer)
            ->setParameter('OrderStatuses', $OrderStatuses)
            ->groupBy('o.Customer')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int $id
     * @param int|OrderStatus $status
     * @return null|object
     */
    public function findWithStatus($id, $status)
    {
        return $this->findOneBy(array('id' => $id, 'OrderStatus' => $status));
    }

    /**
     * @param Customer $Customer
     * @param array $OrderStatuses
     * @return QueryBuilder
     */
    public function getQueryBuilderByOwner(Customer $Customer, array $OrderStatuses = array())
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.OrderDetails', 'od')
            ->leftJoin('od.Product', 'p')
            ->where('p.Creator = :Customer')
            ->setParameter('Customer', $Customer);

        if (count($OrderStatuses) > 0) {
            $qb->andWhere('o.OrderStatus in (:OrderStatuses)')
                ->setParameter('OrderStatuses', $OrderStatuses);
        }

        $qb->groupBy('o.id');

        // Order By
        $qb->addOrderBy('o.receiptable_date', 'ASC');

        return $qb;
    }

    /**
     * @param Customer $Customer
     * @param array $OrderStatuses
     * @return QueryBuilder
     */
    public function getQueryBuilderByReceiver(Customer $Customer, array $OrderStatuses = array())
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.Customer = :Customer')
            ->setParameter('Customer', $Customer);

        if (count($OrderStatuses) > 0) {
            $qb->andWhere('o.OrderStatus in (:OrderStatuses)')
                ->setParameter('OrderStatuses', $OrderStatuses);
        }

//        $qb->groupBy('o.id');

        // Order By
        $qb->addOrderBy('o.receiptable_date', 'ASC');

        return $qb;
    }

    /**
     * @param $id
     * @return array
     * @see BusStopRepository::getByOrder()
     */
    public function getFarmerBusStop($id) {
        $sql =  'SELECT dtb_bus_stop.bus_stop_id, dtb_bus_stop.name, dtb_bus_stop.address, move_time FROM dtb_order '.
                'LEFT JOIN dtb_customer ON dtb_order.farmer_id = dtb_customer.customer_id '.
                'LEFT JOIN dtb_bus_stop ON dtb_customer.bus_stop = dtb_bus_stop.bus_stop_id '.
                'LEFT JOIN dtb_route_detail ON dtb_bus_stop.bus_stop_id = dtb_route_detail.bus_stop_id '.
                'WHERE order_id = ?';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('bus_stop_id', 'bus_stop_id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('address', 'address');
        $rsm->addScalarResult('move_time', 'move_time');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $id);
        $results = $query->getResult();

        return $results;
    }

    public function getSaleByMonth ($farmer_id) {
        //need move to my sql
        $sql =  'SELECT SUM(payment_total) as total, MONTH(order_date) as month '.
                'FROM dtb_order '.
                'WHERE order_date LIKE ? AND farmer_id = ? '.
                'GROUP BY DATE_FORMAT(order_date, "%Y-%m")';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('month', 'month');
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, date('Y%'));
        $query->setParameter(2, $farmer_id);
        $results = $query->getResult();
        dump($results());

        return $results;
    }
}
