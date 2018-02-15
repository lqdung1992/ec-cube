<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/15/2018
 * Time: 23:13 PM
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\ReceiptableDate;

/**
 * Class Version20180215231300
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180215231300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('dtb_product');
        if ($table->hasForeignKey('FK_C49DE22F61220EA6')) {
            $table->removeForeignKey('FK_C49DE22F61220EA6');
        }
//        $table->addForeignKeyConstraint('dtb_customer', array('creator_id'), array('customer_id'), array(), 'FK_C49DE22F61220EA6');

        $table = $schema->getTable('dtb_product_class');
        if ($table->hasForeignKey('FK_1A11D1BA61220EA6')) {
            $table->removeForeignKey('FK_1A11D1BA61220EA6');
        }

        $table = $schema->getTable('dtb_product_stock');
        if ($table->hasForeignKey('FK_BC6C9E4561220EA6')) {
            $table->removeForeignKey('FK_BC6C9E4561220EA6');
        }

        $table = $schema->getTable('dtb_product_tag');
        if ($table->hasForeignKey('FK_4433E72161220EA6')) {
            $table->removeForeignKey('FK_4433E72161220EA6');
        }
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}