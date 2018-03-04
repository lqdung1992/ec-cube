<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 01/03/2018
 * Time: 9:22 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\Master\OrderStatus;

class Version20180301212200 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        /** @var EntityManager $em */
        $em = $app['orm.em'];
        $orderStatus = new OrderStatus();
        $orderStatus->setId(9)
            ->setName('準備する')
            ->setRank(9);
        $em->persist($orderStatus);

        $orderStatus = new OrderStatus();
        $orderStatus->setId(10)
            ->setName('動く')
            ->setRank(10);
        $em->persist($orderStatus);

        $orderStatus = new OrderStatus();
        $orderStatus->setId(11)
            ->setName('受け取った')
            ->setRank(11);
        $em->persist($orderStatus);

        $orderStatus = new OrderStatus();
        $orderStatus->setId(12)
            ->setName('完了')
            ->setRank(12);
        $em->persist($orderStatus);
        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}