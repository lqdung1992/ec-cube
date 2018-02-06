<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/06/2018
 * Time: 06:02 PM
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180206180000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'dtb_customer';

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $this->alterCustomerTable($schema);
    }
    /**
     * Down method
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

    /**
     * @param Schema $schema
     * @return bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function alterCustomerTable(Schema $schema)
    {
        if (!$schema->hasTable(self::table)) {
            return true;
        }
        $table = $schema->getTable(self::table);
        if (!$table->hasColumn('profile_image')) {
            $table->addColumn('profile_image', 'string', array('NotNull' => false, 'length' => 100));
        }

        return true;
    }
}
