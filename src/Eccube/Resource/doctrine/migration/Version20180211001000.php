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

class Version20180211001000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO dtb_block (device_type_id, block_name, file_name, create_date, update_date, logic_flg, deletable_flg) VALUES (10, 'top_main_content', 'top_main_content', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0, 1);");
        $this->addSql("INSERT INTO dtb_block (device_type_id, block_name, file_name, create_date, update_date, logic_flg, deletable_flg) VALUES (10, 'farmer_top_header', 'farmer_top_header', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0, 1);");
        $this->addSql("INSERT INTO dtb_block (device_type_id, block_name, file_name, create_date, update_date, logic_flg, deletable_flg) VALUES (10, 'new_footer', 'new_footer', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0, 1);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
