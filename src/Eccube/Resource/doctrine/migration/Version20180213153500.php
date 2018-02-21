<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/13/2018
 * Time: 3:35 PM
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\ReceiptableDate;

/**
 * Class Version20180213153500
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180213153500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app['orm.em'];
        $unit = new ReceiptableDate();
        $unit->setId(1);
        $unit->setName('月');
        $unit->setRank(1);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(2);
        $unit->setName('火');
        $unit->setRank(2);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(3);
        $unit->setName('水');
        $unit->setRank(3);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(4);
        $unit->setName('木');
        $unit->setRank(4);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(5);
        $unit->setName('金');
        $unit->setRank(5);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(6);
        $unit->setName('土');
        $unit->setRank(6);
        $em->persist($unit);

        $unit = new ReceiptableDate();
        $unit->setId(7);
        $unit->setName('日');
        $unit->setRank(7);
        $em->persist($unit);

        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}