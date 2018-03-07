<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/13/2018
 * Time: 11:40 AM
 */

namespace Eccube\Repository\Master;


use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Master\ReceiptableDate;

class ReceiptableDateRepository extends EntityRepository
{
    /**
     * @return ReceiptableDate[]
     */
    public function findAllWithKeyAsId()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('rd')
            ->from('Eccube\Entity\Master\ReceiptableDate', 'rd', 'rd.id')
            ->getQuery()
            ->getResult();
    }
}
