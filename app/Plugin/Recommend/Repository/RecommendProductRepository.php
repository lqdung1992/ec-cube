<?php
/*
 * This file is part of the Recommend Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Recommend\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Master\Disp;
use Plugin\Recommend\Entity\RecommendProduct;

/**
 * RecommendProductRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecommendProductRepository extends EntityRepository
{
    /**
     * Find list.
     *
     * @return mixed
     */
    public function getRecommendList()
    {
        $qb = $this->createQueryBuilder('rp')
            ->innerJoin('rp.Product', 'p');
        $qb->addOrderBy('rp.rank', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get max rank.
     *
     * @return mixed
     */
    public function getMaxRank()
    {
        // 最大のランクを取得する.
        $sql = "SELECT MAX(m.rank) AS max_rank FROM Plugin\Recommend\Entity\RecommendProduct m";
        $q = $this->getEntityManager()->createQuery($sql);

        return $q->getSingleScalarResult();
    }

    /**
     * Get recommend product by display status of product.
     *
     * @param Disp $Disp
     *
     * @return array
     */
    public function getRecommendProduct(Disp $Disp)
    {
        $qb = $this->createQueryBuilder('rp')
            ->innerJoin('rp.Product', 'p')
            ->where('p.Status = :Disp')
            ->orderBy('rp.rank', 'DESC')
            ->setParameter('Disp', $Disp);

        $now = new \DateTime();
        $now = $now->format('Y-m-d 00:00:00');
        $qb->innerJoin('Eccube\Entity\ProductClass', 'pc', 'WITH', 'p.id = pc.Product');
        $qb->andWhere(':date <= pc.production_end_date')
            ->setParameter('date', new \DateTime($now), \Doctrine\DBAL\Types\Type::DATETIME);

        return $qb->getQuery()->getResult();
    }

    /**
     * Number of recommend.
     *
     * @return int
     */
    public function countRecommend()
    {
        $qb = $this->createQueryBuilder('rp');
        $qb->select('COUNT(rp)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Move rank.
     *
     * @param array $arrRank
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function moveRecommendRank(array $arrRank)
    {
        $this->getEntityManager()->beginTransaction();
        $arrRankMoved = array();
        try {
            foreach ($arrRank as $recommendId => $rank) {
                /* @var $Recommend RecommendProduct */
                $Recommend = $this->find($recommendId);
                if ($Recommend->getRank() == $rank) {
                    continue;
                }
                $arrRankMoved[$recommendId] = $rank;
                $Recommend->setRank($rank);
                $this->getEntityManager()->persist($Recommend);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }

        return $arrRankMoved;
    }

    /**
     * Save recommend.
     *
     * @param RecommendProduct $RecommendProduct
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function saveRecommend(RecommendProduct $RecommendProduct)
    {
        $this->getEntityManager()->beginTransaction();
        try {
            $this->getEntityManager()->persist($RecommendProduct);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Get all id of recommend product.
     *
     * @return array
     */
    public function getRecommendProductIdAll()
    {
        $query = $this->createQueryBuilder('rp')
            ->select('IDENTITY(rp.Product) as id')
            ->getQuery();
        $arrReturn = $query->getScalarResult();

        return array_map('current', $arrReturn);
    }
}
