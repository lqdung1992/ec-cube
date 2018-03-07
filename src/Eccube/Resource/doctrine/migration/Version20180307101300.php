<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 03/07/2018
 * Time: 10:13
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Application;

/**
 * Class Version20180307101300
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180307101300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('dtb_product');
        if ($table->hasForeignKey('FK_C49DE22F4584665A')) {
            $table->removeForeignKey('FK_C49DE22F4584665A');
        }
        if ($table->hasIndex('UNIQ_C49DE22F4584665A')) {
            $table->dropIndex('UNIQ_C49DE22F4584665A');
        }

        $this->addSql('DELETE From dtb_product_category where product_id in (1,2);');
        $this->addSql('DELETE From dtb_product_image where product_id in (1,2);');
        $this->addSql('DELETE From dtb_product_stock where product_class_id <= 10;');
        $this->addSql('DELETE From dtb_product_tag where product_id in (1,2);');
        $this->addSql('DELETE From dtb_product_class where product_class_id <= 10;');
        $this->addSql('DELETE From dtb_customer_favorite_product where product_id in (1,2);');
        $this->addSql('DELETE From dtb_product where product_id in (1,2);');
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}