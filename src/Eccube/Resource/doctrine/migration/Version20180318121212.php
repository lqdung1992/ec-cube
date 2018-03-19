<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 03/14/2018
 * Time: 16:09
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20180318121212
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180318121212 extends AbstractMigration
{

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        //add container_type
        $table = $schema->getTable('dtb_customer');
        if (!$table->hasColumn('receiver_container_type')) {
            $this->addSql('ALTER TABLE dtb_customer ADD receiver_container_type SMALLINT ;');
        }
        if (!$table->hasColumn('receiver_get_time')) {
            $this->addSql('ALTER TABLE dtb_customer ADD receiver_get_time SMALLINT ;');
        }

    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}