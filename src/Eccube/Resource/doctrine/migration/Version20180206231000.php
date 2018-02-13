<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/06/2018
 * Time: 23:30
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Repository\Master\CustomerRoleRepository;

class Version20180206231000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        /** @var CustomerRoleRepository $repo */
        $repo = $app['eccube.repository.master.customer_role'];
        /** @var CustomerRole $role */
        $role = $repo->find(1);
        if ($role) {
            $role->setId(1)
                ->setName('ROLE_DRIVER')
                ->setRank(1)
                ->setNameJp('ドライバー');
            $em->persist($role);
        }

        /** @var CustomerRole $role */
        $role = $repo->find(2);
        if ($role) {
            $role->setId(2)
                ->setName('ROLE_FARMER')
                ->setRank(2)
                ->setNameJp('生産者');
            $em->persist($role);
        }

        /** @var CustomerRole $role */
        $role = $repo->find(3);
        if (!$role) {
            $role = new CustomerRole();
            $role->setId(3)
                ->setName('ROLE_RECIPIENT')
                ->setRank(3)
                ->setNameJp('受領者');
            $em->persist($role);
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
