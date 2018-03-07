<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 2/28/2018
 * Time: 9:52 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180228215200 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('dtb_order');
        if (!$table->hasColumn('receiptable_date')) {
            $table->addColumn('receiptable_date', 'datetime', array('NotNull' => false));
        }
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}