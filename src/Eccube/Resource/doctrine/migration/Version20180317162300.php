<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 17/03/2018
 * Time: 16:23
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\ProductListOrderBy;

/**
 * Class Version20180317162300
 * @package DoctrineMigrations
 */
class Version20180317162300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $productOrder = new ProductListOrderBy();
        $productOrder->setId(4)
            ->setName('早く届く順')
            ->setRank(3);
        $em->persist($productOrder);

        $productOrder = new ProductListOrderBy();
        $productOrder->setId(5)
            ->setName('人気が高い順')
            ->setRank(4);
        $em->persist($productOrder);
        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}