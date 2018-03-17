<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/15/2018
 * Time: 9:07 PM
 */

namespace Eccube\Repository\Master;


use Doctrine\ORM\EntityRepository;

/**
 * Class SearchTypeRepository
 * @package Eccube\Repository\Master
 */
class SearchTypeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllIdAsKey()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()->select('st')
            ->from('Eccube\Entity\Master\SearchType', 'st', 'st.id')
            ->orderBy('st.rank', 'ASC');

        return $qb->getQuery()->getArrayResult();
    }
}
