<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 09/03/2018
 * Time: 10:29 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20180309222900
 * @package DoctrineMigrations
 */
class Version20180309222900 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('dtb_product_receiptable_date');
        if ($table->hasColumn('date')) {
            return;
        }
        $this->addSql("DROP TABLE dtb_product_receiptable_date;");
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}