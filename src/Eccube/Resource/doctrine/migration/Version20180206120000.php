<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/06/2018
 * Time: 13:00
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\PageLayout;

class Version20180206120000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        $customerRole = new CustomerRole();
        $customerRole->setId(1)
            ->setName('ROLE_USER')
            ->setRank(1);
        $em->persist($customerRole);

        $customerRole = new CustomerRole();
        $customerRole->setId(2)
            ->setName('ROLE_FARMER')
            ->setRank(2);
        $em->persist($customerRole);

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
