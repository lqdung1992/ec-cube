<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 15:29
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Repository\BlockPositionRepository;

class Version20180210152900 extends AbstractMigration
{
    /**
     * Remove page of login
     *
     * @param Schema $schema
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        /** @var BlockPositionRepository $repo */
        $repo = $app['eccube.repository.block_position'];
        // remove all header footer
        $blockPos = $repo->findAll();
        if ($blockPos) {
            foreach ($blockPos as $pos) {
                $em->remove($pos);
            }
            $em->flush();
        }
    }
    /**
     * Down method
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
