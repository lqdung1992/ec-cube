<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/21/2018
 * Time: 12:13
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20180221121300
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180221121300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('dtb_product_image');
        if ($table->hasForeignKey('FK_3267CC7A61220EA6')) {
            $table->removeForeignKey('FK_3267CC7A61220EA6');
        }
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}