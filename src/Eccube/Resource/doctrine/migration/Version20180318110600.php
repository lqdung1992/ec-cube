<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 18/03/2018
 * Time: 11:06
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Master\ProductListOrderBy;

/**
 * Class Version20180318110600
 * @package DoctrineMigrations
 */
class Version20180318110600 extends AbstractMigration
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

        $orderStatus = new OrderStatus();
        $orderStatus->setId(OrderStatus::PICKUP_DONE)
            ->setName('集荷完了')
            ->setRank(13);
        $em->persist($orderStatus);

        $em->persist($orderStatus);
        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}