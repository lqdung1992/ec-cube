<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/08/2018
 * Time: 15:23
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Entity\Master\ApprovalStatus;
use Eccube\Repository\Master\ApprovalStatusRepository;

class Version20180208152600 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        /** @var ApprovalStatusRepository $repo */
        $repo = $app['eccube.repository.master.approval_status'];
        $approvalStatus = $repo->find(1);
        if (!$approvalStatus) {
            $approvalStatus = new ApprovalStatus();
            $approvalStatus->setId(1)
                ->setName('審査中')
                ->setRank(1);
            $em->persist($approvalStatus);
        }

        $approvalStatus = $repo->find(2);
        if (!$approvalStatus) {
            $approvalStatus = new ApprovalStatus();
            $approvalStatus->setId(2)
                ->setName('審査OK')
                ->setRank(2);
            $em->persist($approvalStatus);
        }

        $approvalStatus = $repo->find(3);
        if (!$approvalStatus) {
            $approvalStatus = new ApprovalStatus();
            $approvalStatus->setId(3)
                ->setName('審査NG')
                ->setRank(3);
            $em->persist($approvalStatus);
        }

        $approvalStatus = $repo->find(4);
        if (!$approvalStatus) {
            $approvalStatus = new ApprovalStatus();
            $approvalStatus->setId(4)
                ->setName('完了')
                ->setRank(4);
            $em->persist($approvalStatus);
        }

        $em->flush();
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
