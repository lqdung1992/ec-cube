<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 04/03/2018
 * Time: 8:09 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;

class Version20180304200900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $DevideType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        $block = new Block();
        $block->setName('top')
            ->setDeviceType($DevideType)
            ->setFileName('top')
            ->setLogicFlg(0)
            ->setDeletableFlg(1);
        $em->persist($block);
        $em->flush();

        $PageTop = $app['eccube.repository.page_layout']->find(1);
        $blockPos = new BlockPosition();
        $blockPos->setPageLayout($PageTop)
            ->setPageId(1)
            ->setAnywhere(1)
            ->setBlockRow(1)
            ->setBlock($block)
            ->setBlockId($block->getId())
            ->setTargetId(5);

        $em->persist($blockPos);
        $block->addBlockPosition($blockPos);
        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}